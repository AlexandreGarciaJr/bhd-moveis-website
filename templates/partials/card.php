<?php
/**
 * Card de produto.
 * @var array $p
 * @var string $sizes  atributo sizes da imagem
 */
$d = desconto($p);
$vitrine = preco_vitrine($p);
$tamanhos = $sizes ?? '(max-width: 760px) 75vw, (max-width: 1200px) 33vw, 25vw';
?>
<article class="card" data-card data-preco="<?= $vitrine['valor'] ?>" data-desconto="<?= $d ?>" data-destaque="<?= !empty($p['destaque']) ? 1 : 0 ?>">
  <a class="card-link" href="<?= e(url($p['url'])) ?>">
    <div class="card-foto">
      <?= imagem_produto($p, 0, ['sizes' => $tamanhos, 'alt' => '', 'classe' => 'card-foto-1']) ?>
      <?php if (count($p['imagens']) > 1): ?>
      <?= imagem_produto($p, 1, ['sizes' => $tamanhos, 'alt' => '', 'classe' => 'card-foto-2']) ?>
      <?php endif; ?>
      <?php if ($d): ?><span class="selo selo-desconto">−<?= $d ?>%</span><?php endif; ?>
      <?php if (!$d && !empty($p['oferta'])): ?><span class="selo selo-desconto">Oferta</span><?php endif; ?>
      <?php if (pronta_entrega($p)): ?><span class="selo selo-pronta"><i></i>Pronta-entrega</span><?php endif; ?>
      <?php partial('etiqueta', ['p' => $p, 'tamanho' => 'p']); ?>
    </div>
    <h3 class="card-nome"><span><?= e($p['nome']) ?></span></h3>
    <p class="card-preco"><?php if ($vitrine['a_partir']): ?><small>a partir de</small><?php endif; ?>R$&nbsp;<?= preco($vitrine['valor']) ?><?php if ($d && $vitrine['valor'] === (float) $p['preco']): ?><s>R$ <?= preco((float) $p['preco_anterior']) ?></s><?php endif; ?><span>à vista, <?= e(texto_parcela($p, $vitrine['valor'])) ?></span></p>
    <?php if (!empty($p['resumo'])): ?><p class="card-resumo"><?= e($p['resumo']) ?></p><?php endif; ?>
  </a>
  <a class="btn btn-contorno btn-pequeno btn-largo card-pedido" href="<?= e(pedido_whatsapp($p)) ?>" target="_blank" rel="noopener"
     data-track="click_pedido_whatsapp" data-local="card" data-item="<?= e($p['id']) ?>" aria-label="Pedir <?= e($p['nome']) ?> pelo WhatsApp">
    <?= icone('whatsapp') ?><span>Pedir<span class="card-pedido-resto"> pelo WhatsApp</span></span>
  </a>
</article>
