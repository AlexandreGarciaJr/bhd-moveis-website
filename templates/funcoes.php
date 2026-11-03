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

/** Texto do pedido que já abre pronto na conversa do WhatsApp. */
/**
 * Texto do pedido que já abre pronto na conversa do WhatsApp.
 * $escolhas: ['Tecido' => 'Linho', 'Tamanho' => '1,80 m'] (as opções escolhidas na página)
 */
function mensagem_pedido(array $p, array $escolhas = []): string
{
    $linhas = ['Olá! Quero fazer um pedido pelo site da BHD Móveis:', '', '*' . $p['nome'] . '*'];
    foreach ($escolhas as $nome => $valor) {
        $linhas[] = $nome . ': ' . $valor;
    }
    // Se cada tamanho já traz o próprio preço, ele vai na linha "Tamanho" e o valor geral sairia contraditório.
    $tamanho = current(array_filter($p['opcoes'] ?? [], fn($g) => $g['nome'] === 'Tamanho')) ?: null;
    $tamanhoComPreco = $tamanho && str_contains(implode(' ', $tamanho['valores']), 'R$');
    if (!$tamanhoComPreco) {
        $referencia = $tamanho ? ' (tamanho ' . $tamanho['valores'][$tamanho['padrao'] ?? 0] . ')' : '';
        $linhas[] = 'Valor à vista' . $referencia . ': R$ ' . preco((float) $p['preco']) . ' (' . texto_parcela($p) . ')';
    }
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

/** "ou 18x de R$ 189,00" (valor informado pela loja) ou "em até 18x no cartão". */
function texto_parcela(array $p): string
{
    $qtd = (int) ($p['parcelas']['qtd'] ?? bhd('site')['parcelas_max']);
    if (!empty($p['parcelas']['valor'])) {
        return 'ou ' . $qtd . 'x de R$ ' . number_format((float) $p['parcelas']['valor'], 2, ',', '.');
    }
    return 'em até ' . $qtd . 'x no cartão';
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
    ];
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
