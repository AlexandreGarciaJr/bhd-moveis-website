<?php
/** @var string $conteudo */
$site = bhd('site');
$pg = bhd('pagina');
$indexavel = $site['ambiente'] === 'producao' && empty($pg['noindex']);
$imagemOg = url_abs('assets/' . ($pg['imagem'] ?? 'img/ambientes/hero-1600.webp'));
$scripts = $pg['scripts'] ?? ['gsap'];

// Configuração lida pelo JavaScript (analytics e interface)
$config = [
    'base' => url('/'),
    'whatsapp' => whatsapp(),
    'analytics' => [
        'ga4' => $site['analytics']['ga4']['ativo'] ? $site['analytics']['ga4']['id'] : '',
        'pixel' => $site['analytics']['meta_pixel']['ativo'] ? $site['analytics']['meta_pixel']['id'] : '',
        'clarity' => $site['analytics']['clarity']['ativo'] ? $site['analytics']['clarity']['id'] : '',
    ],
    'popup' => $site['avaliacoes']['popup'],
    'produto' => isset($pg['produto']) ? ['id' => $pg['produto']['id'], 'nome' => $pg['produto']['nome'], 'preco' => $pg['produto']['preco'], 'categoria' => $pg['produto']['categoria']] : null,
];
?><!doctype html>
<html lang="<?= e($site['idioma']) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= e($pg['titulo']) ?></title>
<meta name="description" content="<?= e($pg['descricao']) ?>">
<?php if ($indexavel): ?>
<link rel="canonical" href="<?= e(url_abs($pg['caminho'])) ?>">
<?php else: ?>
<meta name="robots" content="noindex, nofollow">
<?php endif; ?>
<meta name="theme-color" content="#6D012B">

<meta property="og:type" content="<?= isset($pg['produto']) ? 'product' : 'website' ?>">
<meta property="og:site_name" content="<?= e($site['nome']) ?>">
<meta property="og:locale" content="pt_BR">
<meta property="og:title" content="<?= e($pg['titulo']) ?>">
<meta property="og:description" content="<?= e($pg['descricao']) ?>">
<meta property="og:url" content="<?= e(url_abs($pg['caminho'])) ?>">
<meta property="og:image" content="<?= e($imagemOg) ?>">
<meta name="twitter:card" content="summary_large_image">

<link rel="icon" href="<?= e(url('assets/img/marca/favicon.svg')) ?>" type="image/svg+xml">
<link rel="apple-touch-icon" href="<?= e(url('assets/img/marca/apple-touch-icon.png')) ?>">
<link rel="manifest" href="<?= e(url('site.webmanifest')) ?>">

<link rel="preload" href="<?= e(url('assets/fonts/bricolage-grotesque.woff2')) ?>" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="<?= e(url('assets/fonts/instrument-sans.woff2')) ?>" as="font" type="font/woff2" crossorigin>
<style>
@font-face{font-family:"Bricolage Grotesque";src:url("<?= e(url('assets/fonts/bricolage-grotesque.woff2')) ?>") format("woff2");font-weight:200 800;font-display:swap}
@font-face{font-family:"Instrument Sans";src:url("<?= e(url('assets/fonts/instrument-sans.woff2')) ?>") format("woff2");font-weight:400 700;font-display:swap}
</style>
<link rel="stylesheet" href="<?= e(asset('css/site.css')) ?>">
<script>
  // Motion só com JS e sem "reduzir movimento". Se o GSAP não carregar em 3s, a página aparece normalmente.
  (function (d) {
    var r = d.documentElement;
    r.classList.add('js');
    if (!matchMedia('(prefers-reduced-motion: reduce)').matches && <?= in_array('gsap', $scripts, true) ? 'true' : 'false' ?>) {
      r.classList.add('motion');
      setTimeout(function () { if (!r.classList.contains('motion-pronto')) r.classList.remove('motion'); }, 3000);
    }
  })(document);
  window.BHD = <?= json_encode($config, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
</script>
<?php foreach ($pg['ld'] ?? [] as $ld) echo json_ld($ld), "\n"; ?>
</head>
<body class="<?= e($pg['classe'] ?? '') ?>">
<a class="pular" href="#conteudo">Pular para o conteúdo</a>
<?php partial('sprite'); ?>
<?php partial('header'); ?>

<main id="conteudo" tabindex="-1">
<?= $conteudo ?>
</main>

<?php partial('footer'); ?>
<?php partial('flutuantes'); ?>
<?php partial('busca'); ?>

<?php foreach ($scripts as $s): ?>
<script src="<?= e(asset('js/vendor/' . $s . '.min.js')) ?>" defer></script>
<?php endforeach; ?>
<script src="<?= e(asset('js/app.js')) ?>" defer></script>
<?php if ($scripts): ?>
<script src="<?= e(asset('js/motion.js')) ?>" defer></script>
<?php endif; ?>
</body>
</html>
