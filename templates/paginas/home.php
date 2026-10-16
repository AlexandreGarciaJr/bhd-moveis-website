<?php
$site = bhd('site');
$hero = $site['hero'];
$produtos = bhd('produtos');
$porSlug = array_column($produtos, null, 'slug');
$cenas = array_values(array_filter($hero['cenas'], fn($c) => isset($porSlug[$c['produto']])));
$ofertas = array_values(array_filter($produtos, fn($p) => desconto($p) > 0));
usort($ofertas, fn($a, $b) => desconto($b) <=> desconto($a));
$destaques = array_slice(array_values(array_filter($produtos, fn($p) => !empty($p['destaque']))), 0, 8);
?>
<div class="palco">
  <section class="hero" aria-labelledby="hero-titulo">
    <div class="hero-texto">
      <h1 class="hero-titulo" id="hero-titulo">
        <?php foreach ($hero['titulo'] as $linha): ?><span class="hero-linha"><span><?= e($linha) ?></span></span> <?php endforeach; ?>
      </h1>
      <div class="hero-apoio">
        <p class="hero-lead" data-hero-anim><?= e($hero['texto']) ?></p>
        <div class="hero-ctas" data-hero-anim>
          <a class="btn btn-primario btn-grande" href="<?= e(url('/ofertas/')) ?>" data-magnetico><?= rolar('Ver ofertas da semana') ?></a>
          <a class="btn btn-contorno btn-grande" href="<?= e(url('/sofas/')) ?>" data-magnetico><?= rolar('Explorar sofás') ?></a>
        </div>
        <ul class="hero-lojas" data-hero-anim aria-label="Nossas lojas">
          <?php foreach (bhd('unidades') as $u): ?>
          <li><a href="<?= e(url('/unidades/')) ?>"><?= icone('pin') ?><?= rolar($u['bairro'] === 'Jardim Lúcia' ? 'Jardim Lúcia, SP' : $u['cidade']) ?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>

    <div class="hero-midia" data-cenas data-segundos="<?= (int) ($hero['segundos_por_cena'] ?? 6) ?>">
      <?php foreach ($cenas as $i => $c):
        $p = $porSlug[$c['produto']]; ?>
      <div class="hero-cena<?= $i === 0 ? ' ativa' : '' ?>" data-cena
        style="--ax:<?= (float) $c['ancora'][0] ?>%;--ay:<?= (float) $c['ancora'][1] ?>%;--tx:<?= (float) $c['etiqueta'][0] ?>%;--ty:<?= (float) $c['etiqueta'][1] ?>%"
        <?= $i === 0 ? '' : 'aria-hidden="true" inert' ?>>
        <div class="hero-foto">
          <?= imagem($c['imagem'], 'Ambiente com o ' . $p['nome'], [
              'prioridade' => $i === 0,
              'sizes' => '(max-width: 900px) 100vw, 58vw',
              'attrs' => ['style' => 'object-position:' . ($c['foco'] ?? '50% 50%')],
          ]) ?>
        </div>
        <svg class="hero-cordao" viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true"><path d=""/></svg>
        <span class="hero-ancora" aria-hidden="true"></span>
        <div class="hero-etiqueta">
          <?php partial('etiqueta', ['p' => $p, 'tamanho' => 'm', 'nome' => true, 'link' => true]); ?>
        </div>
      </div>
      <?php endforeach; ?>

      <span class="hero-cortina" aria-hidden="true"></span>
      <?php if ($site['demo']): ?><span class="nota-ilustrativa">Imagem ilustrativa</span><?php endif; ?>
      <span class="hero-escurecer" aria-hidden="true"></span>

      <?php if (count($cenas) > 1): ?>
      <div class="hero-indicadores" role="group" aria-label="Ambientes em destaque">
        <?php foreach ($cenas as $i => $c): ?>
        <button type="button" data-ir-cena="<?= $i ?>" aria-label="Mostrar <?= e($porSlug[$c['produto']]['nome']) ?>"<?= $i === 0 ? ' aria-current="true"' : '' ?>><i></i></button>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
  </section>

  <section class="folha categorias" aria-labelledby="cat-titulo">
    <h2 class="titulo-secao" id="cat-titulo">O que você procura?</h2>
    <ul class="categorias-grade">
      <?php foreach (bhd('categorias') as $c): ?>
      <li>
        <a class="categoria" href="<?= e(url('/' . $c['slug'] . '/')) ?>">
          <?= imagem('produtos/' . $c['capa'], '', ['sizes' => '(max-width: 760px) 50vw, 25vw']) ?>
          <span class="categoria-texto"><strong><?= rolar($c['nome']) ?></strong><span class="categoria-seta"><?= icone('seta') ?></span></span>
        </a>
      </li>
      <?php endforeach; ?>
    </ul>
  </section>
</div>

<section class="secao ofertas" aria-labelledby="ofertas-titulo">
  <div class="secao-cabeca">
    <div>
      <h2 class="titulo-secao" id="ofertas-titulo">Ofertas da semana</h2>
      <p class="secao-sub">Peças com preço reduzido, prontas para entrega.</p>
    </div>
    <div class="secao-ferramentas">
      <a class="link-sublinhado" href="<?= e(url('/ofertas/')) ?>">Ver todas as ofertas</a>
      <div class="trilho-setas">
        <button class="btn-icone" type="button" data-trilho-anterior aria-label="Ofertas anteriores" disabled><?= icone('seta-esq') ?></button>
        <button class="btn-icone" type="button" data-trilho-proximo aria-label="Próximas ofertas"><?= icone('seta') ?></button>
      </div>
    </div>
  </div>
  <div class="trilho" data-trilho tabindex="0" aria-label="Ofertas da semana, role para o lado">
    <?php foreach ($ofertas as $p) partial('card', ['p' => $p, 'sizes' => '(max-width: 760px) 75vw, 24vw']); ?>
  </div>
  <div class="trilho-progresso" aria-hidden="true"><i data-trilho-barra></i></div>
</section>

<section class="canais" aria-labelledby="canais-titulo">
  <h2 class="canais-titulo" id="canais-titulo">Compre do seu jeito</h2>
  <ul class="canais-lista">
    <li>
      <?= icone('loja') ?>
      <h3>Na loja</h3>
      <p>Veja, sente e leve. São duas lojas, no Jardim Lúcia e em Cotia.</p>
      <a href="<?= e(url('/unidades/')) ?>"><?= rolar('Ver endereços') ?><?= icone('seta') ?></a>
    </li>
    <li>
      <?= icone('whatsapp') ?>
      <h3>Pelo WhatsApp</h3>
      <p>Tire dúvidas, peça fotos e feche a compra sem sair de casa.</p>
      <a href="<?= e(whatsapp()) ?>" target="_blank" rel="noopener" data-track="click_whatsapp" data-local="canais"><?= rolar('Chamar agora') ?><?= icone('seta') ?></a>
    </li>
    <li>
      <?= icone('sacola') ?>
      <h3>No Mercado Livre ou na Amazon</h3>
      <p>Muitos produtos também estão nos marketplaces. O link aparece na página de cada um.</p>
      <a href="<?= e(url('/ofertas/')) ?>"><?= rolar('Ver produtos') ?><?= icone('seta') ?></a>
    </li>
  </ul>
</section>

<?php $palavras = ['Sofás', 'Poltronas', 'Mesas', 'Cadeiras', 'Buffets', 'Racks', 'Cômodas', 'Aparadores']; ?>
<div class="faixa" aria-hidden="true">
  <?php foreach (['faixa-linha', 'faixa-linha faixa-linha-contorno'] as $k => $classe): ?>
  <div class="<?= $classe ?>" data-faixa="<?= $k ? -1 : 1 ?>">
    <?php for ($r = 0; $r < 2; $r++): foreach ($k ? array_reverse($palavras) : $palavras as $w): ?>
    <span><?= e($w) ?></span><i class="faixa-etiqueta"></i>
    <?php endforeach; endfor; ?>
  </div>
  <?php endforeach; ?>
</div>

<section class="secao destaques" aria-labelledby="destaques-titulo">
  <div class="secao-cabeca">
    <div>
      <h2 class="titulo-secao" id="destaques-titulo">Mais procurados</h2>
      <p class="secao-sub">Os modelos que mais saem nas nossas lojas.</p>
    </div>
  </div>
  <div class="grade-produtos">
    <?php foreach ($destaques as $p) partial('card', ['p' => $p]); ?>
  </div>
</section>

<section class="secao showroom" aria-labelledby="showroom-titulo">
  <div class="showroom-foto">
    <?= imagem('ambientes/showroom-3', 'Showroom de móveis (imagem ilustrativa)', ['sizes' => '(max-width: 900px) 100vw, 55vw']) ?><?php if ($site['demo']): ?><span class="nota-ilustrativa">Imagem ilustrativa</span><?php endif; ?>
  </div>
  <div class="showroom-texto">
    <h2 class="titulo-secao" id="showroom-titulo">Venha ver de perto</h2>
    <p>Sente no sofá, abra as gavetas, compare os tecidos. São duas lojas com os móveis montados para você ver como ficam.</p>
    <?php partial('lojas', ['variante' => 'lista']); ?>
  </div>
</section>

<section class="avalie" aria-labelledby="avalie-titulo">
  <?= icone('estrela') ?>
  <div>
    <h2 id="avalie-titulo">Comprou com a gente? Avalie sua loja.</h2>
    <p>Leva menos de um minuto e ajuda outras pessoas a conhecerem a BHD.</p>
  </div>
  <div class="avalie-acoes">
    <?php foreach (bhd('unidades') as $u): ?>
    <a class="btn btn-contorno" href="<?= e($u['avaliar']) ?>" target="_blank" rel="noopener" data-track="click_avaliacao" data-local="<?= e($u['slug']) ?>"><?= e($u['nome']) ?><?= icone('externo') ?></a>
    <?php endforeach; ?>
  </div>
</section>

<?php partial('faq'); ?>

<?= json_ld([
    '@context' => 'https://schema.org',
    '@type' => 'WebSite',
    'name' => $site['nome'],
    'url' => url_abs('/'),
]) ?>
<?php foreach (bhd('unidades') as $u) echo json_ld(ld_loja($u)); ?>
