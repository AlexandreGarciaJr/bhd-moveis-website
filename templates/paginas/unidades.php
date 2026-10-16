<nav class="migalhas" aria-label="Você está em">
  <ol>
    <li><a href="<?= e(url('/')) ?>">Início</a></li>
    <li aria-current="page">Nossas lojas</li>
  </ol>
</nav>

<header class="lista-cabeca">
  <h1 class="titulo-pagina">Nossas lojas</h1>
  <p class="lista-descricao">Duas lojas com os móveis montados para você ver de perto, sentar e comparar. Chame no WhatsApp antes de ir e confira se o modelo que você quer está na loja.</p>
</header>

<?php partial('lojas', ['variante' => 'cards']); ?>

<section class="avalie avalie-unidades" aria-labelledby="avalie-titulo">
  <?= icone('estrela') ?>
  <div>
    <h2 id="avalie-titulo">Já comprou na BHD?</h2>
    <p>Conte como foi no Google. Sua avaliação ajuda outras pessoas a escolherem.</p>
  </div>
  <div class="avalie-acoes">
    <?php foreach (bhd('unidades') as $u): ?>
    <a class="btn btn-contorno" href="<?= e($u['avaliar']) ?>" target="_blank" rel="noopener" data-track="click_avaliacao" data-local="<?= e($u['slug']) ?>">Avaliar <?= e($u['nome']) ?><?= icone('externo') ?></a>
    <?php endforeach; ?>
  </div>
</section>
