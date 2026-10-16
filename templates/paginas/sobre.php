<?php $site = bhd('site'); ?>
<section class="sobre-abertura">
  <div class="sobre-texto">
    <h1 class="titulo-pagina">Móveis bons, preço justo e pronta-entrega.</h1>
    <p>A BHD Móveis tem lojas no Jardim Lúcia, em São Paulo, e em Cotia. Trabalhamos com sofás, mesas, cadeiras e móveis para toda a casa, com estoque nas lojas para você levar sem esperar.</p>
    <p>Você pode comprar como preferir: direto na loja, pelo WhatsApp ou pelos nossos anúncios no Mercado Livre e na Amazon.</p>
    <?php if ($site['demo']): ?><p class="nota-provisoria">Texto provisório, a ser revisado com a BHD.</p><?php endif; ?>
  </div>
  <div class="sobre-foto"><?= imagem('ambientes/showroom-1', 'Showroom de móveis (imagem ilustrativa)', ['sizes' => '(max-width: 900px) 100vw, 50vw', 'prioridade' => true]) ?></div>
</section>

<ul class="sobre-pilares">
  <?php foreach ($site['beneficios'] as $b): ?>
  <li><?= icone($b['icone']) ?><strong><?= e($b['destaque']) ?></strong><span><?= e($b['texto']) ?></span></li>
  <?php endforeach; ?>
</ul>

<section class="secao">
  <h2 class="titulo-secao">Onde estamos</h2>
  <?php partial('lojas', ['variante' => 'lista']); ?>
</section>
