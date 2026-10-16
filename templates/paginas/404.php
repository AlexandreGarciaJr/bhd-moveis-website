<section class="erro-404">
  <p class="erro-codigo" aria-hidden="true">404</p>
  <h1 class="titulo-pagina">Essa página não existe mais.</h1>
  <p>O produto pode ter saído do catálogo ou o endereço mudou. Veja o que temos hoje:</p>
  <ul class="erro-categorias">
    <?php foreach (bhd('categorias') as $c): ?>
    <li><a class="btn btn-contorno" href="<?= e(url('/' . $c['slug'] . '/')) ?>"><?= icone($c['icone']) ?><?= e($c['nome']) ?></a></li>
    <?php endforeach; ?>
    <li><a class="btn btn-primario" href="<?= e(url('/ofertas/')) ?>">Ofertas da semana</a></li>
  </ul>
  <button class="link-sublinhado" type="button" data-abrir="busca">Ou busque um produto</button>
</section>
