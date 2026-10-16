<?php $site = bhd('site'); ?>
<footer class="rodape">
  <div class="rodape-grade">
    <div class="rodape-marca">
      <img src="<?= e(url('assets/img/marca/logo-bhd-branco.svg')) ?>" alt="BHD Móveis &amp; Planejados" width="200" height="84" loading="lazy">
      <p>Móveis com preço de outlet e pronta-entrega em São Paulo e Cotia.</p>
      <div class="rodape-redes">
        <a class="btn-icone btn-icone-claro" href="<?= e($site['contato']['instagram']) ?>" target="_blank" rel="noopener" aria-label="Instagram da BHD Móveis"><?= icone('instagram') ?></a>
        <a class="btn-icone btn-icone-claro" href="<?= e(whatsapp()) ?>" target="_blank" rel="noopener" aria-label="WhatsApp da BHD Móveis" data-track="click_whatsapp" data-local="rodape"><?= icone('whatsapp') ?></a>
      </div>
    </div>

    <nav class="rodape-col" aria-label="Categorias">
      <h2>Produtos</h2>
      <ul>
        <?php foreach (bhd('categorias') as $c): ?>
        <li><a href="<?= e(url('/' . $c['slug'] . '/')) ?>"><?= e($c['nome']) ?></a></li>
        <?php endforeach; ?>
        <li><a href="<?= e(url('/ofertas/')) ?>">Ofertas da semana</a></li>
      </ul>
    </nav>

    <nav class="rodape-col" aria-label="Institucional">
      <h2>A BHD</h2>
      <ul>
        <li><a href="<?= e(url('/sobre/')) ?>">Sobre</a></li>
        <li><a href="<?= e(url('/unidades/')) ?>">Nossas lojas</a></li>
        <li><a href="<?= e(url('/privacidade/')) ?>">Privacidade</a></li>
      </ul>
    </nav>

    <div class="rodape-col">
      <h2>Lojas</h2>
      <ul class="rodape-lojas">
        <?php foreach (bhd('unidades') as $u): ?>
        <li>
          <strong><?= e($u['nome']) ?></strong>
          <span><?= e($u['endereco']) ?>, <?= e($u['bairro']) ?><br><?= e($u['cidade']) ?> - <?= e($u['uf']) ?></span>
          <a href="<?= e($u['rota']) ?>" target="_blank" rel="noopener" data-track="click_rota" data-local="<?= e($u['slug']) ?>">Como chegar</a>
        </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>

  <div class="rodape-base">
    <p>© <?= date('Y') ?> <?= e($site['nome_completo']) ?>. Preços e condições sujeitos a alteração.</p>
    <?php if ($site['demo']): ?>
    <p class="rodape-demo">Versão de demonstração: produtos, preços e fotos são ilustrativos.</p>
    <?php endif; ?>
  </div>
</footer>
