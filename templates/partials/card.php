<?php
/**
 * Card de produto.
 * @var array $p
 * @var string $sizes  atributo sizes da imagem
 */
$d = desconto($p);
$canais = canais($p);
?>
<article class="card" data-card
  data-preco="<?= (float) $p['preco'] ?>" data-desconto="<?= $d ?>"
  data-destaque="<?= !empty($p['destaque']) ? 1 : 0 ?>" data-pronta="<?= !empty($p['pronta_entrega']) ? 1 : 0 ?>">
  <a class="card-link" href="<?= e(url($p['url'])) ?>">
    <div class="card-foto">
      <?= imagem_produto($p, 0, ['sizes' => $sizes ?? '(max-width: 760px) 75vw, (max-width: 1200px) 33vw, 25vw', 'alt' => '', 'classe' => 'card-foto-1']) ?>
      <?php if (count($p['imagens']) > 1): ?>
      <?= imagem_produto($p, 1, ['sizes' => $sizes ?? '(max-width: 760px) 75vw, (max-width: 1200px) 33vw, 25vw', 'alt' => '', 'classe' => 'card-foto-2']) ?>
      <?php endif; ?>
      <?php if ($d): ?><span class="selo selo-desconto">−<?= $d ?>%</span><?php endif; ?>
      <?php if (!empty($p['pronta_entrega'])): ?><span class="selo selo-pronta"><i></i>Pronta-entrega</span><?php endif; ?>
      <?php partial('etiqueta', ['p' => $p, 'tamanho' => 'p']); ?>
    </div>
    <h3 class="card-nome"><span><?= e($p['nome']) ?></span></h3>
    <p class="card-preco">R$&nbsp;<?= preco((float) $p['preco']) ?><?php if ($d): ?><s>R$ <?= preco((float) $p['preco_anterior']) ?></s><?php endif; ?><span><?= (int) bhd('site')['parcelas_max'] ?>x de R$ <?= parcela((float) $p['preco']) ?></span></p>
    <?php if (!empty($p['resumo'])): ?><p class="card-resumo"><?= e($p['resumo']) ?></p><?php endif; ?>
  </a>
  <p class="card-canais">
    <?php foreach (array_slice($canais, 0, 2) as $c): ?><span class="canal canal-<?= e($c['tipo']) ?>" aria-hidden="true"></span><?php endforeach; ?>
    <?= e(canais_resumo($p)) ?>
  </p>
</article>
