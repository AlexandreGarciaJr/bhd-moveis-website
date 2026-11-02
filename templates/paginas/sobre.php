<?php $site = bhd('site'); ?>
<section class="sobre-abertura">
  <div class="sobre-texto">
    <h1 class="titulo-pagina">Móveis bons, preço justo e pronta-entrega.</h1>
    <p>A BHD Móveis tem duas lojas na Rod. Raposo Tavares, no KM 16 e no KM 29. Trabalhamos com sofás, painéis, racks, mesas e cadeiras à pronta-entrega, com entrega em até 48h para toda a Grande São Paulo.</p>
    <p>Você pode comprar como preferir: direto na loja ou pelo WhatsApp.</p>
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
