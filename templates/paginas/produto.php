<?php
/** @var array $p @var array $cat @var array $sub @var array $relacionados */
$site = bhd('site');
$rotulosMedida = ['largura' => 'Largura', 'profundidade' => 'Profundidade', 'altura' => 'Altura', 'diametro' => 'Diâmetro', 'peso' => 'Peso'];
$variacoes = $p['variacoes'] ?? [];
$primeira = $variacoes[0] ?? null;
$numero = preg_replace('/\D/', '', $site['contato']['whatsapp']);
// o JavaScript troca {{opcao}} pela opção escolhida e atualiza o link do pedido
$atributosPedido = 'href="' . e(pedido_whatsapp($p, $primeira)) . '" target="_blank" rel="noopener" data-track="click_pedido_whatsapp" data-item="' . e($p['id']) . '"';
if ($variacoes) {
    $atributosPedido .= ' data-pedido data-numero="' . e($numero) . '" data-modelo="' . e(mensagem_pedido($p, '{{opcao}}')) . '"';
}
?>
<nav class="migalhas" aria-label="Você está em">
  <ol>
    <li><a href="<?= e(url('/')) ?>">Início</a></li>
    <li><a href="<?= e(url('/' . $cat['slug'] . '/')) ?>"><?= e($cat['nome']) ?></a></li>
    <li><a href="<?= e(url('/' . $cat['slug'] . '/' . $sub['slug'] . '/')) ?>"><?= e($sub['nome']) ?></a></li>
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
    <a class="produto-categoria" href="<?= e(url('/' . $cat['slug'] . '/' . $sub['slug'] . '/')) ?>"><?= e($sub['nome']) ?></a>
    <h1 class="produto-nome"><?= e($p['nome']) ?></h1>
    <?php if (!empty($p['resumo'])): ?><p class="produto-resumo"><?= e($p['resumo']) ?></p><?php endif; ?>

    <div class="produto-preco">
      <?php partial('etiqueta', ['p' => $p, 'tamanho' => 'g']); ?>
      <p class="produto-status"><?= icone('check') ?>Pronta-entrega em até 48h na Grande São Paulo</p>
    </div>

    <?php if ($variacoes): ?>
    <fieldset class="produto-variacoes">
      <legend>Escolha a opção:</legend>
      <div class="variacoes-lista">
        <?php foreach ($variacoes as $i => $v): ?>
        <label class="variacao"><input type="radio" name="variacao" value="<?= e($v) ?>" data-variacao<?= $i === 0 ? ' checked' : '' ?>><span><?= e($v) ?></span></label>
        <?php endforeach; ?>
      </div>
    </fieldset>
    <?php endif; ?>

    <div class="produto-compra">
      <a class="btn btn-primario btn-grande btn-largo" <?= $atributosPedido ?> data-local="produto"><?= icone('whatsapp') ?>Pedir pelo WhatsApp</a>
      <p class="produto-aviso">A mensagem do pedido já abre pronta na conversa, é só enviar. Preço atualizado em <?= e(data_br($p['atualizado_em'] ?? date('Y-m-d'))) ?>.</p>
    </div>

    <div class="produto-lojas">
      <h2><?= icone('loja') ?>Veja na loja</h2>
      <ul>
        <?php foreach (bhd('unidades') as $u): ?>
        <li>
          <span><strong><?= e($u['nome']) ?></strong><?= e($u['referencia']) ?></span>
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
    <h2 class="titulo-secao" id="rel-titulo">Mais para a <?= e(mb_strtolower($cat['nome'])) ?></h2>
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
  <a class="btn btn-primario" <?= $atributosPedido ?> data-local="barra"><?= icone('whatsapp') ?>Pedir pelo WhatsApp</a>
</div>
