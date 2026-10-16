<?php
/** @var array $categoria @var array $lista */
$temPronta = count(array_filter($lista, fn($p) => !empty($p['pronta_entrega']))) > 0;
?>
<nav class="migalhas" aria-label="Você está em">
  <ol>
    <li><a href="<?= e(url('/')) ?>">Início</a></li>
    <li aria-current="page"><?= e($categoria['nome']) ?></li>
  </ol>
</nav>

<header class="lista-cabeca">
  <h1 class="titulo-pagina"><?= e($categoria['nome']) ?></h1>
  <p class="lista-descricao"><?= e($categoria['descricao']) ?></p>
</header>

<div class="lista-ferramentas" data-lista-ferramentas>
  <p class="lista-total" role="status"><span data-lista-total><?= count($lista) ?></span> <?= count($lista) === 1 ? 'produto' : 'produtos' ?></p>
  <div class="lista-controles">
    <?php if ($temPronta): ?>
    <label class="chip-filtro"><input type="checkbox" data-filtro-pronta> Só pronta-entrega</label>
    <?php endif; ?>
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
<p class="lista-vazia" data-lista-vazia hidden>Nenhum produto com pronta-entrega nesta categoria agora. <button type="button" class="link-sublinhado" data-limpar-filtro>Ver todos</button></p>

<section class="outras-categorias" aria-labelledby="outras-titulo">
  <h2 id="outras-titulo">Veja também</h2>
  <ul>
    <?php foreach (bhd('categorias') as $c): if ($c['slug'] === $categoria['slug']) continue; ?>
    <li><a href="<?= e(url('/' . $c['slug'] . '/')) ?>"><?= icone($c['icone']) ?><?= e($c['nome']) ?></a></li>
    <?php endforeach; ?>
    <li><a href="<?= e(url('/ofertas/')) ?>"><?= icone('estrela') ?>Ofertas</a></li>
  </ul>
</section>
