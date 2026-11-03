<?php
/**
 * BHD Móveis — gerador do site.
 *
 * Lê os dados em data/*.json, aplica os templates PHP e gera HTML estático em dist/.
 * O conteúdo de dist/ pode ser publicado em qualquer hospedagem (HostGator, Netlify, Cloudflare Pages...).
 *
 *   php build.php              → preview (noindex, conforme data/site.json)
 *   php build.php --producao   → produção (indexável, sitemap liberado)
 *   php build.php --base=/bhd/ → publica em subpasta
 *   php build.php --url=https://exemplo.com → domínio dos links absolutos (mensagem do pedido, Open Graph)
 */

declare(strict_types=1);

const RAIZ = __DIR__;
const DIST = RAIZ . '/dist';

require RAIZ . '/templates/funcoes.php';

$inicio = microtime(true);
$opcoes = getopt('', ['producao', 'base:', 'url:']);

/* ---------- 1. Dados ---------- */

function ler_json(string $arquivo): array
{
    $caminho = RAIZ . '/data/' . $arquivo;
    $dados = json_decode((string) file_get_contents($caminho), true);
    if (!is_array($dados)) {
        fwrite(STDERR, "ERRO: {$arquivo} não é um JSON válido (" . json_last_error_msg() . ")\n");
        exit(1);
    }
    return $dados;
}

$site = ler_json('site.json');
if (isset($opcoes['producao'])) {
    $site['ambiente'] = 'producao';
}
if (isset($opcoes['base'])) {
    $site['caminho_base'] = '/' . trim($opcoes['base'], '/') . '/';
}

if (isset($opcoes['url'])) {
    $site['url'] = rtrim($opcoes['url'], '/');
}

$categorias = array_values(array_filter(ler_json('categorias.json'), fn($c) => $c['ativo'] ?? true));
usort($categorias, fn($a, $b) => ($a['ordem'] ?? 99) <=> ($b['ordem'] ?? 99));
$categoriasPorSlug = array_column($categorias, null, 'slug');

$produtos = [];
$avisos = [];
foreach (ler_json('produtos.json') as $p) {
    if (!($p['ativo'] ?? true)) {
        continue;
    }
    foreach (['slug', 'nome', 'categoria', 'preco'] as $obrigatorio) {
        if (empty($p[$obrigatorio])) {
            fwrite(STDERR, "ERRO: produto {$p['id']} sem o campo \"{$obrigatorio}\".\n");
            exit(1);
        }
    }
    $catDoProduto = $categoriasPorSlug[$p['categoria']] ?? null;
    if (!$catDoProduto) {
        $avisos[] = "Produto {$p['slug']}: categoria \"{$p['categoria']}\" não existe ou está inativa. Produto ignorado.";
        continue;
    }
    if (!in_array($p['subcategoria'] ?? '', array_column($catDoProduto['subcategorias'] ?? [], 'slug'), true)) {
        $avisos[] = "Produto {$p['slug']}: subcategoria \"" . ($p['subcategoria'] ?? '') . "\" não existe em {$p['categoria']}. Produto ignorado.";
        continue;
    }
    $p['url'] = '/' . $p['categoria'] . '/' . $p['subcategoria'] . '/' . $p['slug'] . '/';
    $p['imagens'] = $p['imagens'] ?? ['01.jpg'];
    foreach ($p['imagens'] as $img) {
        $nome = pathinfo($img, PATHINFO_FILENAME);
        if (!is_file(RAIZ . "/assets/img/produtos/{$p['slug']}/{$nome}-960.webp")) {
            $avisos[] = "Produto {$p['slug']}: imagem {$img} não encontrada. Rode \"npm run imagens\".";
        }
    }
    $produtos[] = $p;
}

$unidades = ler_json('unidades.json');

$versao = substr(md5((string) time()), 0, 8);

$GLOBALS['BHD'] = compact('site', 'categorias', 'produtos', 'unidades', 'versao');

/* ---------- 2. Limpa dist/ e copia assets ---------- */

/**
 * Esvazia a pasta. Pastas em uso (servidor local, OneDrive, Explorer) podem não ser removidas
 * no Windows; nesse caso ficam vazias e são reaproveitadas.
 */
function limpar_pasta(string $pasta): void
{
    if (!is_dir($pasta)) {
        return;
    }
    $itens = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($pasta, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($itens as $item) {
        $item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
    }
}

function copiar_pasta(string $de, string $para): void
{
    @mkdir($para, 0777, true);
    foreach (new DirectoryIterator($de) as $item) {
        if ($item->isDot()) {
            continue;
        }
        $destino = $para . '/' . $item->getFilename();
        $item->isDir() ? copiar_pasta($item->getPathname(), $destino) : copy($item->getPathname(), $destino);
    }
}

function gravar(string $caminho, string $conteudo): void
{
    $arquivo = DIST . '/' . ltrim($caminho, '/');
    if (str_ends_with($arquivo, '/')) {
        $arquivo .= 'index.html';
    }
    @mkdir(dirname($arquivo), 0777, true);
    file_put_contents($arquivo, $conteudo);
}

limpar_pasta(DIST);
@mkdir(DIST, 0777, true);
foreach (['fonts', 'img', 'js/vendor', 'video'] as $pasta) {
    if (!is_dir(RAIZ . "/assets/{$pasta}")) {
        continue;
    }
    copiar_pasta(RAIZ . "/assets/{$pasta}", DIST . "/assets/{$pasta}");
}

// CSS: junta os arquivos na ordem e remove comentários e espaços extras
$css = '';
foreach (['tokens', 'base', 'layout', 'componentes', 'paginas', 'interacoes'] as $parte) {
    $css .= file_get_contents(RAIZ . "/assets/css/{$parte}.css") . "\n";
}
$css = preg_replace('!/\*.*?\*/!s', '', $css);
$css = preg_replace('/\s*\n\s*/', "\n", $css);
gravar('assets/css/site.css', trim($css) . "\n");

// JS: o app (analytics + interface) num arquivo; motion separado, só onde há GSAP
gravar('assets/js/app.js', file_get_contents(RAIZ . '/assets/js/analytics.js') . "\n" . file_get_contents(RAIZ . '/assets/js/main.js'));
gravar('assets/js/motion.js', file_get_contents(RAIZ . '/assets/js/motion.js'));

/* ---------- 3. Páginas ---------- */

/**
 * Renderiza uma página dentro do layout.
 * $meta: titulo, descricao, caminho, imagem (OG), scripts (lista de vendors GSAP), ld (JSON-LD extras), classe
 */
function pagina(string $template, array $meta, array $vars = []): string
{
    $GLOBALS['BHD']['pagina'] = $meta;
    ob_start();
    extract($vars, EXTR_SKIP);
    include RAIZ . '/templates/paginas/' . $template . '.php';
    $conteudo = ob_get_clean();
    ob_start();
    include RAIZ . '/templates/layout.php';
    return ob_get_clean();
}

$total = 0;
$sitemap = [];
$registrar = function (string $caminho, string $html, float $prioridade = 0.6) use (&$total, &$sitemap) {
    gravar($caminho, $html);
    $total++;
    if ($caminho !== '404.html') {
        $sitemap[] = [$caminho, $prioridade];
    }
};

// Home
$registrar('/', pagina('home', [
    'titulo' => 'BHD Móveis | Móveis com preço de outlet na Grande São Paulo',
    'descricao' => $site['descricao'],
    'caminho' => '/',
    'scripts' => ['gsap', 'ScrollTrigger'],
    'classe' => 'pg-home',
]), 1.0);

// Categorias
foreach ($categorias as $cat) {
    $lista = produtos_da_categoria($cat['slug']);
    $registrar("/{$cat['slug']}/", pagina('categoria', [
        'titulo' => "{$cat['titulo']} | BHD Móveis",
        'descricao' => $cat['descricao'],
        'caminho' => "/{$cat['slug']}/",
        'imagem' => 'img/produtos/' . $cat['capa'] . '-1440.webp',
        'scripts' => ['gsap'],
        'classe' => 'pg-categoria',
        'ld' => [ld_breadcrumb([['Início', '/'], [$cat['nome'], "/{$cat['slug']}/"]])],
    ], ['categoria' => $cat, 'lista' => $lista, 'sub' => null]), 0.8);

    foreach (subcategorias_ativas($cat) as $sub) {
        $listaSub = array_values(array_filter($lista, fn($p) => $p['subcategoria'] === $sub['slug']));
        $caminho = "/{$cat['slug']}/{$sub['slug']}/";
        $registrar($caminho, pagina('categoria', [
            'titulo' => "{$sub['nome']} | {$cat['nome']} | BHD Móveis",
            'descricao' => "{$sub['nome']} para {$cat['nome']} com preço de outlet e pronta-entrega em até 48h na Grande São Paulo. Peça pelo WhatsApp.",
            'caminho' => $caminho,
            'imagem' => 'img/produtos/' . $listaSub[0]['slug'] . '/' . pathinfo($listaSub[0]['imagens'][0], PATHINFO_FILENAME) . '-1440.webp',
            'scripts' => ['gsap'],
            'classe' => 'pg-categoria',
            'ld' => [ld_breadcrumb([['Início', '/'], [$cat['nome'], "/{$cat['slug']}/"], [$sub['nome'], $caminho]])],
        ], ['categoria' => $cat, 'lista' => $listaSub, 'sub' => $sub]), 0.8);
    }
}

// Produtos
foreach ($produtos as $p) {
    $cat = categoria($p['categoria']);
    $sub = subcategoria($cat, $p['subcategoria']);
    $ld = [ld_breadcrumb([['Início', '/'], [$cat['nome'], "/{$cat['slug']}/"], [$sub['nome'], "/{$cat['slug']}/{$sub['slug']}/"], [$p['nome'], $p['url']]])];
    if (!$site['demo']) {
        $ld[] = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $p['nome'],
            'description' => $p['descricao'] ?? $p['resumo'] ?? '',
            'sku' => $p['id'],
            'image' => array_map(fn($i) => url_abs('assets/img/produtos/' . $p['slug'] . '/' . pathinfo($i, PATHINFO_FILENAME) . '-1440.webp'), $p['imagens']),
            'brand' => ['@type' => 'Brand', 'name' => $site['nome']],
            'offers' => ['@type' => 'Offer', 'priceCurrency' => 'BRL', 'price' => $p['preco'], 'url' => url_abs($p['url']),
                'availability' => pronta_entrega($p) ? 'https://schema.org/InStock' : 'https://schema.org/PreOrder'],
        ];
    }
    // primeiro os da mesma subcategoria, depois o resto da categoria
    $relacionados = array_values(array_filter(produtos_da_categoria($p['categoria']), fn($r) => $r['slug'] !== $p['slug']));
    usort($relacionados, fn($a, $b) => ($b['subcategoria'] === $p['subcategoria']) <=> ($a['subcategoria'] === $p['subcategoria']));
    $registrar($p['url'], pagina('produto', [
        'titulo' => ($p['seo']['titulo'] ?? $p['nome'] . ' por R$ ' . preco((float) $p['preco'])) . ' | BHD Móveis',
        'descricao' => $p['seo']['descricao'] ?? ($p['nome'] . '. ' . ($p['resumo'] ?? '') . ' R$ ' . preco((float) $p['preco']) . ' à vista, ' . texto_parcela($p) . '. ' . (pronta_entrega($p) ? 'Pronta-entrega em até 48h na Grande São Paulo.' : 'Sob encomenda.') . ' Peça pelo WhatsApp.'),
        'caminho' => $p['url'],
        'imagem' => 'img/produtos/' . $p['slug'] . '/' . pathinfo($p['imagens'][0], PATHINFO_FILENAME) . '-1440.webp',
        'scripts' => ['gsap', 'Flip'],
        'classe' => 'pg-produto',
        'ld' => $ld,
        'produto' => $p,
    ], ['p' => $p, 'cat' => $cat, 'sub' => $sub, 'relacionados' => array_slice($relacionados, 0, 4)]), 0.7);
}

// Ofertas
$ofertas = array_values(array_filter($produtos, fn($p) => desconto($p) > 0));
usort($ofertas, fn($a, $b) => desconto($b) <=> desconto($a));
$registrar('/ofertas/', pagina('ofertas', [
    'titulo' => 'Ofertas da semana | BHD Móveis',
    'descricao' => 'Sofás, painéis, racks, mesas e cadeiras com desconto e pronta-entrega em até 48h na Grande São Paulo.',
    'caminho' => '/ofertas/',
    'scripts' => ['gsap'],
    'classe' => 'pg-ofertas',
], ['lista' => $ofertas]), 0.9);

// Unidades
$registrar('/unidades/', pagina('unidades', [
    'titulo' => 'Nossas lojas na Raposo Tavares | BHD Móveis',
    'descricao' => 'Endereços, horários, WhatsApp e como chegar às lojas da BHD Móveis na Rod. Raposo Tavares, KM 16 e KM 29.',
    'caminho' => '/unidades/',
    'classe' => 'pg-unidades',
    'ld' => array_map('ld_loja', $unidades),
]), 0.8);

// Institucionais
$registrar('/sobre/', pagina('sobre', [
    'titulo' => 'Sobre a BHD Móveis',
    'descricao' => 'Conheça a BHD Móveis: lojas na Raposo Tavares com móveis à pronta-entrega e preço de outlet na Grande São Paulo.',
    'caminho' => '/sobre/',
    'classe' => 'pg-sobre',
]), 0.4);
$registrar('/privacidade/', pagina('privacidade', [
    'titulo' => 'Política de privacidade | BHD Móveis',
    'descricao' => 'Como a BHD Móveis trata dados pessoais e cookies neste site.',
    'caminho' => '/privacidade/',
    'classe' => 'pg-texto',
]), 0.2);
$registrar('404.html', pagina('404', [
    'titulo' => 'Página não encontrada | BHD Móveis',
    'descricao' => 'A página que você procurou não existe ou mudou de endereço.',
    'caminho' => '/404.html',
    'classe' => 'pg-404',
    'noindex' => true,
]));

/* ---------- 4. Arquivos de apoio ---------- */

// Índice da busca
$busca = array_map(fn($p) => [
    'n' => $p['nome'],
    'c' => subcategoria(categoria($p['categoria']), $p['subcategoria'])['nome'] . ', ' . categoria($p['categoria'])['nome'],
    'r' => $p['resumo'] ?? '',
    'p' => preco((float) $p['preco']),
    'u' => url($p['url']),
    'i' => asset('img/produtos/' . $p['slug'] . '/' . pathinfo($p['imagens'][0], PATHINFO_FILENAME) . '-480.webp'),
], $produtos);
gravar('busca.json', json_encode($busca, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

$indexavel = $site['ambiente'] === 'producao';

// robots.txt
gravar('robots.txt', $indexavel
    ? "User-agent: *\nAllow: /\n\nSitemap: " . url_abs('sitemap.xml') . "\n"
    : "# Preview: não indexar\nUser-agent: *\nDisallow: /\n");

// sitemap.xml
$hoje = date('Y-m-d');
$xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($sitemap as [$caminho, $prioridade]) {
    $xml .= '  <url><loc>' . e(url_abs($caminho)) . "</loc><lastmod>{$hoje}</lastmod><priority>" . number_format($prioridade, 1) . "</priority></url>\n";
}
gravar('sitemap.xml', $xml . "</urlset>\n");

// Manifest
gravar('site.webmanifest', json_encode([
    'name' => $site['nome'],
    'short_name' => 'BHD',
    'start_url' => url('/'),
    'display' => 'browser',
    'background_color' => '#FBFAF8',
    'theme_color' => '#6D012B',
    'icons' => [['src' => url('assets/img/marca/icone-512.png'), 'sizes' => '512x512', 'type' => 'image/png']],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

// .htaccess (HostGator / Apache)
copy(RAIZ . '/templates/htaccess.txt', DIST . '/.htaccess');

/* ---------- 5. Resumo ---------- */

$tempo = round(microtime(true) - $inicio, 2);
echo "BHD Móveis — build concluído em {$tempo}s\n";
echo "  Ambiente: {$site['ambiente']}" . ($indexavel ? '' : ' (noindex)') . "\n";
echo "  Caminho base: {$site['caminho_base']}\n";
echo "  Páginas: {$total} | Produtos: " . count($produtos) . " | Categorias: " . count($categorias) . "\n";
foreach ($avisos as $a) {
    echo "  AVISO: {$a}\n";
}
