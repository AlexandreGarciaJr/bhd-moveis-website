<?php
/** @var array $p @var array $cat @var array $relacionados */
$site = bhd('site');
$canais = canais($p);
$principal = $canais[0];
$secundarios = array_slice($canais, 1);
$rotulosMedida = ['largura' => 'Largura', 'profundidade' => 'Profundidade', 'altura' => 'Altura', 'diametro' => 'Diâmetro', 'peso' => 'Peso'];
?>
<nav class="migalhas" aria-label="Você está em">
  <ol>
    <li><a href="<?= e(url('/')) ?>">Início</a></li>
    <li><a href="<?= e(url('/' . $cat['slug'] . '/')) ?>"><?= e($cat['nome']) ?></a></li>
    <li aria-current="page"><?= e($p['nome']) ?></li>
  </ol>
</nav>

<div class="produto" data-produto="<?= e($p['slug']) ?>">
  <div class="galeria" data-galeria>
    <div class="galeria-principal">
      <?= imagem_produto($p, 0, ['prioridade' => true, 'sizes' => '(max-width: 900px) 100vw, 55vw', 'classe' => 'galeria-img', 'attrs' => ['data-galeria-principal' => '']]) ?>
      <?php if (desconto($p)): ?><span class="selo selo-desconto">−<?= desconto($p) ?>%</span><?php endif; ?>
      <?php if ($site['demo']): ?><span class="nota-ilustrativa">Imagem ilustrativa</span><?php endif; ?>
    </div>
    <?php if (count($p['imagens']) > 1): ?>
    <ul class="galeria-miniaturas" aria-label="Outras fotos">
      <?php foreach ($p['imagens'] as $i => $img):
        $base = 'produtos/' . $p['slug'] . '/' . pathinfo($img, PATHINFO_FILENAME); ?>
      <li>
        <button type="button" data-galeria-mini data-src="<?= e(asset('img/' . $base . '-1440.webp')) ?>" data-srcset="<?= e(asset('img/' . $base . '-960.webp')) ?> 960w, <?= e(asset('img/' . $base . '-1440.webp')) ?> 1440w"<?= $i === 0 ? ' aria-current="true"' : '' ?> aria-label="Ver foto <?= $i + 1 ?>">
          <?= imagem_produto($p, $i, ['sizes' => '96px', 'alt' => '']) ?>
        </button>
      </li>
      <?php endforeach; ?>
    </ul>
    <?php endif; ?>
  </div>

  <div class="produto-info">
    <a class="produto-categoria" href="<?= e(url('/' . $cat['slug'] . '/')) ?>"><?= e($cat['nome']) ?></a>
    <h1 class="produto-nome"><?= e($p['nome']) ?></h1>
    <?php if (!empty($p['resumo'])): ?><p class="produto-resumo"><?= e($p['resumo']) ?></p><?php endif; ?>

    <div class="produto-preco">
      <?php partial('etiqueta', ['p' => $p, 'tamanho' => 'g']); ?>
      <?php if (!empty($p['pronta_entrega'])): ?>
      <p class="produto-status"><?= icone('check') ?>Pronta-entrega nas lojas</p>
      <?php else: ?>
      <p class="produto-status produto-status-consulta"><?= icone('relogio') ?>Consulte o prazo de entrega</p>
      <?php endif; ?>
    </div>

    <?php if (!empty($p['variacoes'])): ?>
    <div class="produto-variacoes">
      <p>Também disponível em:</p>
      <ul><?php foreach ($p['variacoes'] as $v): ?><li><?= e($v) ?></li><?php endforeach; ?></ul>
    </div>
    <?php endif; ?>

    <div class="produto-compra">
      <a class="btn btn-primario btn-grande btn-largo" href="<?= e($principal['url']) ?>" target="_blank" rel="noopener"
         data-track="<?= e($principal['evento']) ?>" data-local="produto-principal" data-item="<?= e($p['id']) ?>">
        <?= $principal['tipo'] === 'whatsapp' ? icone('whatsapp') : '' ?><?= e($principal['acao']) ?><?= $principal['tipo'] !== 'whatsapp' ? icone('externo') : '' ?>
      </a>
      <?php foreach ($secundarios as $c): ?>
      <a class="btn btn-contorno btn-grande btn-largo" href="<?= e($c['url']) ?>" target="_blank" rel="noopener"
         data-track="<?= e($c['evento']) ?>" data-local="produto-secundario" data-item="<?= e($p['id']) ?>">
        <?= $c['tipo'] === 'whatsapp' ? icone('whatsapp') . 'Tirar dúvidas no WhatsApp' : e($c['acao']) . icone('externo') ?>
      </a>
      <?php endforeach; ?>
      <p class="produto-aviso">O preço pode variar entre a loja e os marketplaces. Atualizado em <?= e(data_br($p['atualizado_em'] ?? date('Y-m-d'))) ?>.</p>
    </div>

    <div class="produto-lojas">
      <h2><?= icone('loja') ?>Veja na loja</h2>
      <ul>
        <?php foreach (bhd('unidades') as $u): ?>
        <li>
          <span><strong><?= e($u['nome']) ?></strong><?= e($u['bairro']) ?>, <?= e($u['cidade']) ?></span>
          <a class="link-sublinhado" href="<?= e($u['rota']) ?>" target="_blank" rel="noopener" data-track="click_rota" data-local="<?= e($u['slug']) ?>">Como chegar</a>
        </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
</div>

<section class="produto-detalhes" aria-label="Detalhes do produto">
  <?php if (!empty($p['descricao'])): ?>
  <div class="detalhe">
    <h2>Descrição</h2>
    <p><?= e($p['descricao']) ?></p>
  </div>
  <?php endif; ?>
  <?php if (!empty($p['medidas'])): ?>
  <div class="detalhe">
    <h2>Medidas</h2>
    <dl class="medidas">
      <?php foreach ($p['medidas'] as $k => $v): ?>
      <div><dt><?= e($rotulosMedida[$k] ?? ucfirst($k)) ?></dt><dd><?= e($v) ?></dd></div>
      <?php endforeach; ?>
    </dl>
  </div>
  <?php endif; ?>
  <?php if (!empty($p['materiais'])): ?>
  <div class="detalhe">
    <h2>Materiais</h2>
    <ul class="materiais"><?php foreach ($p['materiais'] as $m): ?><li><?= e($m) ?></li><?php endforeach; ?></ul>
  </div>
  <?php endif; ?>
</section>

<?php if ($relacionados): ?>
<section class="secao relacionados" aria-labelledby="rel-titulo">
  <div class="secao-cabeca">
    <h2 class="titulo-secao" id="rel-titulo">Mais em <?= e(mb_strtolower($cat['nome'])) ?></h2>
    <a class="link-sublinhado" href="<?= e(url('/' . $cat['slug'] . '/')) ?>">Ver todos</a>
  </div>
  <div class="grade-produtos">
    <?php foreach ($relacionados as $r) partial('card', ['p' => $r]); ?>
  </div>
</section>
<?php endif; ?>

<div class="barra-compra" data-barra-compra>
  <div class="barra-compra-preco">
    <strong>R$ <?= preco((float) $p['preco']) ?></strong>
    <span><?= (int) $site['parcelas_max'] ?>x de R$ <?= parcela((float) $p['preco']) ?></span>
  </div>
  <a class="btn btn-icone-texto btn-contorno" href="<?= e(end($canais)['url']) ?>" target="_blank" rel="noopener" data-track="click_whatsapp" data-local="barra" aria-label="Falar no WhatsApp"><?= icone('whatsapp') ?></a>
  <a class="btn btn-primario" href="<?= e($principal['url']) ?>" target="_blank" rel="noopener" data-track="<?= e($principal['evento']) ?>" data-local="barra" data-item="<?= e($p['id']) ?>" aria-label="<?= e($principal['acao']) ?>">Comprar<?= $principal['tipo'] !== 'whatsapp' ? '<span class="barra-canal">' . e($principal['rotulo']) . '</span>' : '' ?></a>
</div>
