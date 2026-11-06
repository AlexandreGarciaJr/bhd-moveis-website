<?php
/**
 * Funções de apoio usadas pelos templates e pelo build.
 * Os templates recebem as variáveis globais do site via $GLOBALS['BHD'].
 */

declare(strict_types=1);

function bhd(?string $chave = null)
{
    return $chave === null ? $GLOBALS['BHD'] : ($GLOBALS['BHD'][$chave] ?? null);
}

/** Escapa texto para HTML. */
function e(?string $texto): string
{
    return htmlspecialchars((string) $texto, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Caminho interno do site, respeitando o caminho base (ex.: subpasta no preview). */
function url(string $caminho = '/'): string
{
    $base = rtrim(bhd('site')['caminho_base'] ?? '/', '/');
    return $base . '/' . ltrim($caminho, '/');
}

/** URL absoluta (canonical, Open Graph, sitemap). */
function url_abs(string $caminho = '/'): string
{
    return rtrim(bhd('site')['url'], '/') . url($caminho);
}

/** Caminho de um arquivo em assets/, com versão para cache. */
function asset(string $arquivo): string
{
    $v = bhd('versao');
    return url('assets/' . ltrim($arquivo, '/')) . ($v ? '?v=' . $v : '');
}

/** 1899 → "1.899"  |  1899.5 → "1.899,50" */
function preco(float $valor): string
{
    $centavos = round($valor * 100) % 100;
    return number_format($valor, $centavos ? 2 : 0, ',', '.');
}

/** Valor de cada parcela, sempre com centavos. */
function parcela(float $valor): string
{
    $n = (int) (bhd('site')['parcelas_max'] ?? 1);
    return number_format($valor / max($n, 1), 2, ',', '.');
}

function desconto(array $p): int
{
    if (empty($p['preco_anterior']) || $p['preco_anterior'] <= $p['preco']) {
        return 0;
    }
    return (int) round((1 - $p['preco'] / $p['preco_anterior']) * 100);
}

function data_br(string $iso): string
{
    $t = strtotime($iso);
    return $t ? date('d/m/Y', $t) : $iso;
}

/** Link do WhatsApp com mensagem pronta. */
function whatsapp(?string $mensagem = null, ?string $numero = null): string
{
    $site = bhd('site');
    $numero = $numero ?: $site['contato']['whatsapp'];
    $mensagem = $mensagem ?: $site['contato']['mensagem_padrao'];
    return 'https://wa.me/' . preg_replace('/\D/', '', $numero) . '?text=' . rawurlencode($mensagem);
}

/** Ícone do sprite SVG (templates/partials/sprite.php). */
function icone(string $nome, string $classe = 'i'): string
{
    return '<svg class="' . e($classe) . '" aria-hidden="true" focusable="false"><use href="#i-' . e($nome) . '"/></svg>';
}

/**
 * Texto com efeito de "rolagem" no hover (o texto sobe e uma cópia entra por baixo).
 * Puro CSS: a cópia é um text-shadow, então leitores de tela leem o texto uma vez só.
 */
function rolar(string $texto): string
{
    return '<span class="rolar"><span>' . e($texto) . '</span></span>';
}

/** Inclui um partial com variáveis locais. */
function partial(string $_partial, array $vars = []): void
{
    extract($vars, EXTR_SKIP);
    include __DIR__ . '/partials/' . $_partial . '.php';
}

/* ---------- Imagens ---------- */

/** Dimensões reais de um arquivo gerado (para width/height e evitar layout shift). */
function dimensoes(string $relativo): array
{
    static $cache = [];
    if (!isset($cache[$relativo])) {
        $arq = RAIZ . '/assets/' . $relativo;
        $info = is_file($arq) ? @getimagesize($arq) : false;
        $cache[$relativo] = $info ? [$info[0], $info[1]] : [960, 1200];
    }
    return $cache[$relativo];
}

/**
 * <img> responsivo.
 * $base: caminho em assets/img sem sufixo, ex.: "produtos/sofa-x/01-cenario" ou "ambientes/showroom".
 */
function imagem(string $base, string $alt, array $opc = []): string
{
    $grupo = explode('/', $base)[0];
    $larguras = $grupo === 'ambientes' ? [960, 1600, 2400] : [480, 960, 1440];
    $existentes = array_values(array_filter($larguras, fn($w) => is_file(RAIZ . "/assets/img/{$base}-{$w}.webp")));
    if (!$existentes) {
        $existentes = [$larguras[1]];
    }
    $srcset = implode(', ', array_map(fn($w) => asset("img/{$base}-{$w}.webp") . " {$w}w", $existentes));
    $padrao = $existentes[min(1, count($existentes) - 1)];
    [$w, $h] = dimensoes("img/{$base}-{$padrao}.webp");

    $attrs = [
        'src' => asset("img/{$base}-{$padrao}.webp"),
        'srcset' => $srcset,
        'sizes' => $opc['sizes'] ?? '(max-width: 760px) 90vw, 33vw',
        'width' => $w,
        'height' => $h,
        'alt' => $alt,
        'decoding' => 'async',
    ];
    if (!empty($opc['prioridade'])) {
        $attrs['fetchpriority'] = 'high';
    } else {
        $attrs['loading'] = 'lazy';
    }
    if (!empty($opc['classe'])) {
        $attrs['class'] = $opc['classe'];
    }
    if ($h > $w) {
        $attrs['data-retrato'] = '';
    }
    foreach ($opc['attrs'] ?? [] as $k => $v) {
        $attrs[$k] = $v;
    }
    $html = '<img';
    foreach ($attrs as $k => $v) {
        $html .= ' ' . $k . '="' . e((string) $v) . '"';
    }
    return $html . '>';
}

function imagem_produto(array $p, int $indice = 0, array $opc = []): string
{
    $arquivo = $p['imagens'][$indice] ?? $p['imagens'][0] ?? '01.jpg';
    $nome = pathinfo($arquivo, PATHINFO_FILENAME);
    $alt = $opc['alt'] ?? $p['nome'] . ($indice ? ' (foto ' . ($indice + 1) . ')' : '');
    return imagem("produtos/{$p['slug']}/{$nome}", $alt, $opc);
}

/* ---------- Produtos ---------- */

/**
 * Texto do pedido que já abre pronto na conversa do WhatsApp.
 * $escolhas: ['Tecido' => 'Linho', 'Tamanho' => '1,80 m'] (as opções escolhidas na página)
 * $linhaValor: usada pelo modelo do JavaScript ("{{valor}}"); sem ela, sai o valor do tamanho escolhido.
 */
function mensagem_pedido(array $p, array $escolhas = [], ?string $linhaValor = null): string
{
    $linhas = ['Olá! Quero fazer um pedido pelo site da BHD Móveis:', '', '*' . $p['nome'] . '*'];
    foreach ($escolhas as $nome => $valor) {
        $linhas[] = $nome . ': ' . $valor;
    }
    if ($linhaValor === null) {
        $tamanho = grupo_tamanho($p);
        $indice = $tamanho ? array_search($escolhas['Tamanho'] ?? null, $tamanho['valores'], true) : false;
        $linhaValor = linha_valor($p, $indice === false ? (float) $p['preco'] : precos_tamanho($p)[$indice]);
    }
    $linhas[] = $linhaValor;
    $linhas[] = 'Link: ' . url_abs($p['url']);
    if (!empty($p['perguntar'])) {
        $linhas[] = '';
        $linhas[] = $p['perguntar'];
    }
    $linhas[] = '';
    $linhas[] = 'Pode confirmar o pedido e o prazo de entrega?';
    return implode("\n", $linhas);
}

/** Primeira opção de cada grupo (ou a marcada como padrão). */
function escolhas_padrao(array $p): array
{
    $escolhas = [];
    foreach ($p['opcoes'] ?? [] as $grupo) {
        $escolhas[$grupo['nome']] = $grupo['valores'][$grupo['padrao'] ?? 0];
    }
    return $escolhas;
}

/** Link do botão "Pedir pelo WhatsApp". */
function pedido_whatsapp(array $p, ?array $escolhas = null): string
{
    return whatsapp(mensagem_pedido($p, $escolhas ?? escolhas_padrao($p)));
}

/**
 * "ou 18x de R$ 189,00" (valor informado pela loja) ou "em até 18x no cartão".
 * Outro tamanho usa a parcela do grupo Tamanho, se houver; senão mostra só a quantidade.
 */
function texto_parcela(array $p, ?float $valor = null): string
{
    $qtd = (int) ($p['parcelas']['qtd'] ?? bhd('site')['parcelas_max']);
    $parcela = null;
    if ($valor === null || $valor === (float) $p['preco']) {
        $parcela = $p['parcelas']['valor'] ?? null;
    } elseif ($g = grupo_tamanho($p)) {
        // parcela informada para o tamanho que tem esse preço ("parcelas" no grupo Tamanho)
        $i = array_search($valor, precos_tamanho($p), true);
        $parcela = $i === false ? null : ($g['parcelas'][$i] ?? null);
    }
    if ($parcela) {
        return 'ou ' . $qtd . 'x de R$ ' . number_format((float) $parcela, 2, ',', '.');
    }
    return 'em até ' . $qtd . 'x no cartão';
}

/** Linha de valor da mensagem do pedido (null = tamanho sem preço informado). */
function linha_valor(array $p, ?float $valor): string
{
    return $valor === null
        ? 'Valor: a consultar'
        : 'Valor à vista: R$ ' . preco($valor) . ' (' . texto_parcela($p, $valor) . ')';
}

function grupo_tamanho(array $p): ?array
{
    foreach ($p['opcoes'] ?? [] as $g) {
        if ($g['nome'] === 'Tamanho') {
            return $g;
        }
    }
    return null;
}

/** Preço de cada tamanho, na ordem das opções. O tamanho padrão usa o preço principal; null = sob consulta. */
function precos_tamanho(array $p): array
{
    $g = grupo_tamanho($p);
    if (!$g) {
        return [];
    }
    $precos = [];
    foreach ($g['valores'] as $i => $v) {
        $precos[$i] = isset($g['precos'][$i]) ? (float) $g['precos'][$i] : ($i === ($g['padrao'] ?? 0) ? (float) $p['preco'] : null);
    }
    return $precos;
}

/**
 * Preço dos cards e do hero: ['valor' => 2790.0, 'a_partir' => true].
 * "A partir de" quando há preço por tamanho, ou quando o preço é do menor tamanho e há maiores (sob consulta).
 */
function preco_vitrine(array $p): array
{
    $conhecidos = array_filter(precos_tamanho($p), fn($v) => $v !== null);
    if (count($conhecidos) > 1) {
        return ['valor' => min($conhecidos), 'a_partir' => true];
    }
    $g = grupo_tamanho($p);
    if ($g && count($g['valores']) > 1) {
        $metros = array_map(fn($v) => (float) str_replace(',', '.', $v), $g['valores']);
        if ($metros[$g['padrao'] ?? 0] === min($metros)) {
            return ['valor' => (float) $p['preco'], 'a_partir' => true];
        }
    }
    return ['valor' => (float) $p['preco'], 'a_partir' => false];
}

/** Ofertas da semana: produtos com preço "de" e os marcados com "oferta" (o de maior prioridade primeiro). */
function ofertas(): array
{
    $lista = array_values(array_filter(bhd('produtos'), fn($p) => desconto($p) > 0 || !empty($p['oferta'])));
    usort($lista, fn($a, $b) => ($b['prioridade'] ?? 0) <=> ($a['prioridade'] ?? 0) ?: desconto($b) <=> desconto($a));
    return $lista;
}

/** Prazo do produto: pronta-entrega ou sob encomenda. */
function pronta_entrega(array $p): bool
{
    return ($p['entrega'] ?? 'pronta') === 'pronta';
}

/** Aviso sobre a origem da imagem (ambientações e fotos de estúdio geradas por IA). */
function nota_imagem(string $arquivo): string
{
    if (str_contains($arquivo, 'cenario')) {
        return 'Ambientação ilustrativa';
    }
    if (str_contains($arquivo, 'fundo-branco')) {
        return 'Imagem ilustrativa';
    }
    return '';
}

/** Subcategoria pelo slug, dentro de uma categoria. */
function subcategoria(array $categoria, string $slug): ?array
{
    foreach ($categoria['subcategorias'] ?? [] as $sub) {
        if ($sub['slug'] === $slug) {
            return $sub;
        }
    }
    return null;
}

/** Subcategorias que têm pelo menos um produto ativo. */
function subcategorias_ativas(array $categoria): array
{
    return array_values(array_filter($categoria['subcategorias'] ?? [], fn($sub) => count(array_filter(
        bhd('produtos'),
        fn($p) => $p['categoria'] === $categoria['slug'] && ($p['subcategoria'] ?? '') === $sub['slug']
    )) > 0));
}

function produtos_da_categoria(string $slug): array
{
    return array_values(array_filter(bhd('produtos'), fn($p) => $p['categoria'] === $slug));
}

function categoria(string $slug): ?array
{
    foreach (bhd('categorias') as $c) {
        if ($c['slug'] === $slug) {
            return $c;
        }
    }
    return null;
}

/* ---------- SEO ---------- */

/** Descrição do produto para o Google: nome, resumo, preço e prazo; o convite do WhatsApp só se couber. */
function descricao_produto(array $p): string
{
    $v = preco_vitrine($p);
    $texto = $p['nome'] . '. ' . ($p['resumo'] ?? '') . ($v['a_partir'] ? ' A partir de R$ ' : ' R$ ') . preco($v['valor'])
        . ' à vista, ' . texto_parcela($p, $v['valor']) . '. ' . (pronta_entrega($p) ? 'Pronta-entrega em até 48h.' : 'Sob encomenda.');
    return mb_strlen($texto) <= 135 ? $texto . ' Peça pelo WhatsApp.' : $texto;
}

function json_ld(array $dados): string
{
    return '<script type="application/ld+json">' .
        json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) .
        '</script>';
}

function ld_loja(array $u): array
{
    $site = bhd('site');
    $ld = [
        '@context' => 'https://schema.org',
        '@type' => 'FurnitureStore',
        'name' => $u['nome'],
        'url' => url_abs('/unidades/'),
        'telephone' => '+' . $u['whatsapp'],
        'image' => url_abs('assets/img/marca/logo-bhd-circulo-2048.png'),
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => $u['endereco'],
            'addressLocality' => $u['cidade'],
            'addressRegion' => $u['uf'],
            'postalCode' => $u['cep'],
            'addressCountry' => 'BR',
        ],
        'parentOrganization' => ['@type' => 'Organization', 'name' => $site['nome_completo']],
        'priceRange' => '$$',
        // horário das duas lojas: seg-sáb 9h30-18h, domingos e feriados 11h-17h
        'openingHoursSpecification' => [
            ['@type' => 'OpeningHoursSpecification', 'dayOfWeek' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'], 'opens' => '09:30', 'closes' => '18:00'],
            ['@type' => 'OpeningHoursSpecification', 'dayOfWeek' => 'Sunday', 'opens' => '11:00', 'closes' => '17:00'],
        ],
        'sameAs' => array_values(array_filter([$u['google'] ?? null, $site['contato']['instagram'] ?? null])),
    ];
    if (!empty($u['google'])) {
        $ld['hasMap'] = $u['google'];
    }
    if (!empty($u['geo']['lat'])) {
        $ld['geo'] = ['@type' => 'GeoCoordinates', 'latitude' => $u['geo']['lat'], 'longitude' => $u['geo']['lng']];
    }
    return $ld;
}

function ld_breadcrumb(array $itens): array
{
    $lista = [];
    foreach ($itens as $i => [$nome, $caminho]) {
        $lista[] = ['@type' => 'ListItem', 'position' => $i + 1, 'name' => $nome, 'item' => url_abs($caminho)];
    }
    return ['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => $lista];
}
