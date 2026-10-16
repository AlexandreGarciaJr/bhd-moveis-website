<?php
$site = bhd('site');
$atual = bhd('pagina')['caminho'] ?? '/';
$ativo = fn(string $c) => str_starts_with($atual, $c) ? ' aria-current="page"' : '';
$links = array_map(fn($c) => [$c['nome'], '/' . $c['slug'] . '/'], bhd('categorias'));
$links[] = ['Ofertas', '/ofertas/', 'oferta'];
$links[] = ['Lojas', '/unidades/'];
?>
<div class="beneficios">
  <ul class="beneficios-lista">
    <?php foreach ($site['beneficios'] as $b): ?>
    <li><?= icone($b['icone']) ?><span><strong><?= e($b['destaque']) ?></strong> <?= e($b['texto']) ?></span></li>
    <?php endforeach; ?>
  </ul>
</div>

<header class="topo">
  <a class="topo-logo" href="<?= e(url('/')) ?>" aria-label="BHD Móveis, página inicial">
    <img class="logo-completo" src="<?= e(url('assets/img/marca/logo-bhd.svg')) ?>" alt="" width="805" height="337">
    <img class="logo-compacto" src="<?= e(url('assets/img/marca/logo-bhd-compacto.svg')) ?>" alt="" width="805" height="336">
  </a>

  <nav class="topo-nav" aria-label="Principal">
    <ul>
      <?php foreach ($links as $l): ?>
      <li><a href="<?= e(url($l[1])) ?>"<?= isset($l[2]) ? ' class="nav-oferta"' : '' ?><?= $ativo($l[1]) ?>><?= rolar($l[0]) ?></a></li>
      <?php endforeach; ?>
    </ul>
  </nav>

  <div class="topo-acoes">
    <button class="btn-icone" type="button" data-abrir="busca" aria-label="Buscar produtos"><?= icone('busca') ?></button>
    <a class="btn btn-contorno btn-pequeno topo-whats" href="<?= e(whatsapp()) ?>" target="_blank" rel="noopener" data-track="click_whatsapp" data-local="header">
      <?= icone('whatsapp') ?><span class="topo-whats-texto"><?= rolar('WhatsApp') ?></span>
    </a>
    <button class="btn-icone topo-menu" type="button" data-abrir="menu" aria-label="Abrir menu" aria-haspopup="dialog"><?= icone('menu') ?></button>
  </div>
</header>

<dialog class="menu-movel" id="menu" aria-label="Menu">
  <div class="menu-movel-topo">
    <img src="<?= e(url('assets/img/marca/logo-bhd-compacto.svg')) ?>" alt="BHD Móveis" width="96" height="36">
    <button class="btn-icone" type="button" data-fechar aria-label="Fechar menu"><?= icone('fechar') ?></button>
  </div>
  <nav aria-label="Menu principal">
    <ul>
      <?php foreach ($links as $l): ?>
      <li><a href="<?= e(url($l[1])) ?>"<?= $ativo($l[1]) ?>><?= e($l[0]) ?><?= icone('seta') ?></a></li>
      <?php endforeach; ?>
      <li><a href="<?= e(url('/sobre/')) ?>">Sobre a BHD<?= icone('seta') ?></a></li>
    </ul>
  </nav>
  <div class="menu-movel-rodape">
    <button class="btn btn-contorno" type="button" data-abrir="busca"><?= icone('busca') ?>Buscar produtos</button>
    <a class="btn btn-primario" href="<?= e(whatsapp()) ?>" target="_blank" rel="noopener" data-track="click_whatsapp" data-local="menu"><?= icone('whatsapp') ?>Falar no WhatsApp</a>
  </div>
</dialog>
