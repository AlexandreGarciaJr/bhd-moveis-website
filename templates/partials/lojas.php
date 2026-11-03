<?php
/**
 * Lista das lojas com Rota, WhatsApp e Avaliar.
 * @var string $variante 'cards' (página de unidades) ou 'lista' (home e produto)
 */
$variante = $variante ?? 'lista';
?>
<ul class="lojas lojas-<?= e($variante) ?>">
  <?php foreach (bhd('unidades') as $u): ?>
  <li class="loja">
    <?php if ($variante === 'cards'): ?>
    <div class="loja-foto"><?= imagem('ambientes/' . $u['foto'], 'Showroom da loja ' . $u['nome'], ['sizes' => '(max-width: 760px) 100vw, 50vw']) ?></div>
    <?php endif; ?>
    <div class="loja-info">
      <h3 class="loja-nome"><?= e($u['nome']) ?></h3>
      <p class="loja-referencia"><?= e($u['referencia']) ?></p>
      <p class="loja-endereco"><?= icone('pin') ?><span><?= e($u['endereco']) ?>, <?= e($u['bairro']) ?><br><?= e($u['cidade']) ?> - <?= e($u['uf']) ?>, <?= e($u['cep']) ?></span></p>
      <p class="loja-horario"><?= icone('relogio') ?><span><?php if ($u['horarios']): foreach ($u['horarios'] as $h): ?><?= e($h) ?><br><?php endforeach; else: ?>Consulte o horário pelo WhatsApp<?php endif; ?></span></p>
      <?php if ($variante === 'cards'): ?>
      <p class="loja-telefone"><?= icone('whatsapp') ?><span><?= e($u['telefone']) ?></span></p>
      <?php endif; ?>
      <div class="loja-acoes">
        <a class="btn btn-primario btn-pequeno" href="<?= e($u['rota']) ?>" target="_blank" rel="noopener" data-track="click_rota" data-local="<?= e($u['slug']) ?>"><?= icone('rota') ?>Como chegar</a>
        <a class="btn btn-contorno btn-pequeno" href="<?= e(whatsapp('Olá! Gostaria de atendimento na loja ' . $u['nome'] . '.', $u['whatsapp'])) ?>" target="_blank" rel="noopener" data-track="click_whatsapp" data-local="<?= e($u['slug']) ?>"><?= icone('whatsapp') ?>WhatsApp</a>
        <a class="link-sublinhado" href="<?= e($u['avaliar']) ?>" target="_blank" rel="noopener" data-track="click_avaliacao" data-local="<?= e($u['slug']) ?>">Avaliar no Google</a>
      </div>
    </div>
  </li>
  <?php endforeach; ?>
</ul>
