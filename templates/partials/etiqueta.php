<?php
/**
 * Etiqueta de preço (hang tag), elemento de marca da BHD.
 * @var array $p        produto
 * @var string $tamanho 'p' (card), 'm' (hero), 'g' (página de produto)
 * @var bool $nome      mostra o nome do produto na etiqueta
 * @var bool $link      mostra o link "Ver este produto"
 * Fora da página do produto mostra o preço de vitrine ("a partir de" quando há vários tamanhos).
 * Na página do produto, o JavaScript troca valor, preço "de" e parcela quando a pessoa escolhe outro tamanho.
 */
$tamanho = $tamanho ?? 'p';
$nome = $nome ?? false;
$link = $link ?? false;
$vitrine = $tamanho === 'g' ? ['valor' => (float) $p['preco'], 'a_partir' => false] : preco_vitrine($p);
$cheio = $vitrine['valor'] === (float) $p['preco'];
?>
<div class="etiqueta etiqueta-<?= e($tamanho) ?>" data-etiqueta>
  <?php if ($nome): ?><p class="etiqueta-nome"><?= e($p['nome']) ?></p><?php endif; ?>
  <?php if ($vitrine['a_partir']): ?><p class="etiqueta-apartir">a partir de</p><?php endif; ?>
  <p class="etiqueta-preco">
    <span class="sr">Preço: </span><small>R$</small><span data-etiqueta-valor><?= preco($vitrine['valor']) ?></span>
    <?php if (desconto($p) && $cheio): ?><s data-etiqueta-de><span class="sr">de </span>R$ <?= preco((float) $p['preco_anterior']) ?></s><?php endif; ?>
  </p>
  <p class="etiqueta-parcela" data-etiqueta-parcela><span class="etiqueta-avista">à vista</span> <?= e(texto_parcela($p, $vitrine['valor'])) ?></p>
  <?php if ($link): ?><a class="etiqueta-link" href="<?= e(url($p['url'])) ?>">Ver este produto</a><?php endif; ?>
</div>
