<?php
/**
 * Etiqueta de preço (hang tag), elemento de marca da BHD.
 * @var array $p        produto
 * @var string $tamanho 'p' (card), 'm' (hero), 'g' (página de produto)
 * @var bool $nome      mostra o nome do produto na etiqueta
 * @var bool $link      mostra o link "Ver este produto"
 */
$tamanho = $tamanho ?? 'p';
$nome = $nome ?? false;
$link = $link ?? false;
$tag = $link ? 'div' : 'div';
?>
<div class="etiqueta etiqueta-<?= e($tamanho) ?>" data-etiqueta>
  <?php if ($nome): ?><p class="etiqueta-nome"><?= e($p['nome']) ?></p><?php endif; ?>
  <p class="etiqueta-preco">
    <span class="sr">Preço: </span><small>R$</small><?= preco((float) $p['preco']) ?>
    <?php if (desconto($p)): ?><s><span class="sr">de </span>R$ <?= preco((float) $p['preco_anterior']) ?></s><?php endif; ?>
  </p>
  <p class="etiqueta-parcela">ou <?= (int) bhd('site')['parcelas_max'] ?>x de R$ <?= parcela((float) $p['preco']) ?></p>
  <?php if ($link): ?><a class="etiqueta-link" href="<?= e(url($p['url'])) ?>">Ver este produto</a><?php endif; ?>
</div>
