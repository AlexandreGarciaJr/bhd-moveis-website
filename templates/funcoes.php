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
 * $base: caminho em assets/img sem sufixo, ex.: "produtos/sofa-x/01" ou "ambientes/hero".
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

const CANAIS = [
    'mercadolivre' => ['rotulo' => 'Mercado Livre', 'acao' => 'Comprar no Mercado Livre', 'evento' => 'click_mercadolivre'],
    'amazon' => ['rotulo' => 'Amazon', 'acao' => 'Comprar na Amazon', 'evento' => 'click_amazon'],
    'whatsapp' => ['rotulo' => 'WhatsApp', 'acao' => 'Comprar pelo WhatsApp', 'evento' => 'click_whatsapp'],
];

/** Canais de compra do produto, com o principal primeiro. WhatsApp está sempre disponível. */
function canais(array $p): array
{
    $lista = [];
    foreach (['mercadolivre', 'amazon'] as $c) {
        if (!empty($p['links'][$c])) {
            $lista[$c] = CANAIS[$c] + ['tipo' => $c, 'url' => $p['links'][$c]];
        }
    }
    $msg = 'Olá! Tenho interesse no produto "' . $p['nome'] . '" (R$ ' . preco((float) $p['preco']) . '). ' . url_abs($p['url']);
    $lista['whatsapp'] = CANAIS['whatsapp'] + ['tipo' => 'whatsapp', 'url' => whatsapp($msg)];

    $principal = $p['canal_principal'] ?? 'mercadolivre';
    if (isset($lista[$principal])) {
        $lista = [$principal => $lista[$principal]] + $lista;
    }
    return array_values($lista);
}

/** Texto curto "Mercado Livre ou WhatsApp" para os cards. */
function canais_resumo(array $p): string
{
    $nomes = array_map(fn($c) => $c['rotulo'], canais($p));
    if (count($nomes) === 1) {
        return 'Só pelo WhatsApp';
    }
    // nos cards, o WhatsApp só aparece quando há apenas um marketplace
    if (count($nomes) > 2) {
        $nomes = array_values(array_filter($nomes, fn($n) => $n !== 'WhatsApp'));
    }
    return implode(' ou ', $nomes);
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
