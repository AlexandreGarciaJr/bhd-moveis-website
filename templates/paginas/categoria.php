<?php
/** @var array $categoria @var array $lista @var array|null $sub */
$subs = subcategorias_ativas($categoria);
$baseCat = '/' . $categoria['slug'] . '/';
?>
<nav class="migalhas" aria-label="Você está em">
  <ol>
    <li><a href="<?= e(url('/')) ?>">Início</a></li>
    <?php if ($sub): ?>
    <li><a href="<?= e(url($baseCat)) ?>"><?= e($categoria['nome']) ?></a></li>
    <li aria-current="page"><?= e($sub['nome']) ?></li>
    <?php else: ?>
    <li aria-current="page"><?= e($categoria['nome']) ?></li>
    <?php endif; ?>
  </ol>
</nav>

<header class="lista-cabeca">
  <h1 class="titulo-pagina"><?= e($sub ? $sub['nome'] : $categoria['nome']) ?></h1>
  <p class="lista-descricao"><?= e($categoria['descricao']) ?></p>
</header>

<?php if (count($subs) > 1): ?>
<nav class="subnav" aria-label="Tipos de móvel em <?= e($categoria['nome']) ?>">
  <a href="<?= e(url($baseCat)) ?>"<?= $sub ? '' : ' aria-current="page"' ?>>Todos</a>
  <?php foreach ($subs as $s): ?>
  <a href="<?= e(url($baseCat . $s['slug'] . '/')) ?>"<?= ($sub && $sub['slug'] === $s['slug']) ? ' aria-current="page"' : '' ?>><?= e($s['nome']) ?></a>
  <?php endforeach; ?>
</nav>
<?php endif; ?>

<div class="lista-ferramentas" data-lista-ferramentas>
  <p class="lista-total" role="status"><span data-lista-total><?= count($lista) ?></span> <?= count($lista) === 1 ? 'produto' : 'produtos' ?></p>
  <div class="lista-controles">
    <label class="ordenar">
      <span>Ordenar por</span>
      <select data-ordenar>
        <option value="destaque">Destaques</option>
        <option value="menor">Menor preço</option>
        <option value="maior">Maior preço</option>
        <option value="desconto">Maior desconto</option>
      </select>
    </label>
  </div>
</div>

<div class="grade-produtos grade-lista" data-lista>
  <?php foreach ($lista as $p) partial('card', ['p' => $p]); ?>
</div>

<section class="outras-categorias" aria-labelledby="outras-titulo">
  <h2 id="outras-titulo">Veja também</h2>
  <ul>
    <?php foreach (bhd('categorias') as $c): if ($c['slug'] === $categoria['slug']) continue; ?>
    <li><a href="<?= e(url('/' . $c['slug'] . '/')) ?>"><?= icone($c['icone']) ?><?= e($c['nome']) ?></a></li>
    <?php endforeach; ?>
    <li><a href="<?= e(url('/ofertas/')) ?>"><?= icone('estrela') ?>Ofertas</a></li>
  </ul>
</section>
