<dialog class="busca" id="busca" aria-label="Buscar produtos">
  <form class="busca-form" role="search" onsubmit="return false">
    <label class="sr" for="busca-campo">Buscar produtos</label>
    <?= icone('busca') ?>
    <input id="busca-campo" type="search" placeholder="Buscar sofá, mesa, cadeira..." autocomplete="off" data-busca-url="<?= e(url('busca.json')) ?>">
    <button class="btn-icone" type="button" data-fechar aria-label="Fechar busca"><?= icone('fechar') ?></button>
  </form>
  <p class="busca-status" role="status" aria-live="polite"></p>
  <ul class="busca-resultados"></ul>
  <div class="busca-atalhos">
    <span>Categorias:</span>
    <?php foreach (bhd('categorias') as $c): ?>
    <a href="<?= e(url('/' . $c['slug'] . '/')) ?>"><?= e($c['nome']) ?></a>
    <?php endforeach; ?>
  </div>
</dialog>
