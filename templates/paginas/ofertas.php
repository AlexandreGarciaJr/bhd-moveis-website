<?php /** @var array $lista */ ?>
<header class="ofertas-cabeca">
  <div>
    <h1 class="titulo-pagina">Ofertas da semana</h1>
    <p class="lista-descricao">Peças com preço reduzido e pronta-entrega em até 48h na Grande São Paulo. Peça direto pelo WhatsApp.</p>
  </div>
  <a class="btn btn-primario" href="<?= e(whatsapp('Olá! Vi as ofertas da semana no site e gostaria de mais informações.')) ?>" target="_blank" rel="noopener" data-track="click_whatsapp" data-local="ofertas"><?= icone('whatsapp') ?>Tirar dúvidas no WhatsApp</a>
</header>

<div class="lista-ferramentas" data-lista-ferramentas>
  <p class="lista-total" role="status"><span data-lista-total><?= count($lista) ?></span> ofertas</p>
  <div class="lista-controles">
    <label class="ordenar">
      <span>Ordenar por</span>
      <select data-ordenar>
        <option value="desconto">Maior desconto</option>
        <option value="menor">Menor preço</option>
        <option value="maior">Maior preço</option>
      </select>
    </label>
  </div>
</div>

<div class="grade-produtos grade-lista" data-lista>
  <?php foreach ($lista as $p) partial('card', ['p' => $p]); ?>
</div>

<?php partial('faq'); ?>
