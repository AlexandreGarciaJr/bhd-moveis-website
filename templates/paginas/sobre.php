<?php $site = bhd('site'); ?>
<section class="sobre-abertura">
  <div class="sobre-texto">
    <h1 class="titulo-pagina">Móveis bons, preço justo e loja para ver de perto.</h1>
    <p>A BHD Móveis tem duas lojas na Rod. Raposo Tavares, no KM 16 e no KM 29. Trabalhamos com sofás, poltronas, puffs e mesas com preço de outlet. Os itens de pronta-entrega chegam em até 48h em toda a Grande São Paulo, e os demais saem sob encomenda.</p>
    <p>Você pode comprar como preferir: direto na loja ou pelo WhatsApp.</p>
    <?php if ($site['demo']): ?><p class="nota-provisoria">Texto provisório, a ser revisado com a BHD.</p><?php endif; ?>
  </div>
  <div class="sobre-foto"><?= imagem('ambientes/loja-jardim-lucia', 'Showroom da loja BHD Jardim Lúcia', ['sizes' => '(max-width: 900px) 100vw, 50vw', 'prioridade' => true]) ?></div>
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
