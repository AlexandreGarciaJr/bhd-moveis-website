<?php
/** @var array $p @var array $cat @var array $sub @var array $relacionados */
$site = bhd('site');
$rotulosMedida = ['largura' => 'Largura', 'profundidade' => 'Profundidade', 'altura' => 'Altura', 'diametro' => 'Diâmetro', 'peso' => 'Peso'];
$opcoes = $p['opcoes'] ?? [];
$numero = preg_replace('/\D/', '', $site['contato']['whatsapp']);
// o JavaScript troca {{0}}, {{1}}... pelas opções escolhidas e atualiza o link do pedido
$modelo = [];
foreach ($opcoes as $i => $grupo) {
    $modelo[$grupo['nome']] = '{{' . $i . '}}';
}
$atributosPedido = 'href="' . e(pedido_whatsapp($p)) . '" target="_blank" rel="noopener" data-track="click_pedido_whatsapp" data-item="' . e($p['id']) . '"';
if ($opcoes) {
    $atributosPedido .= ' data-pedido data-numero="' . e($numero) . '" data-modelo="' . e(mensagem_pedido($p, $modelo, '{{valor}}')) . '"';
}
$precosTamanho = precos_tamanho($p);
$qtdParcelas = (int) ($p['parcelas']['qtd'] ?? $site['parcelas_max']);
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
      <span class="nota-ilustrativa" data-galeria-nota><?= e(nota_imagem($p['imagens'][0])) ?></span>
      <?php if (!empty($p['video'])): ?>
      <video class="galeria-video" data-galeria-video muted loop playsinline controls preload="none" hidden
             poster="<?= e(asset('video/' . pathinfo($p['video'], PATHINFO_FILENAME) . '.jpg')) ?>">
        <source src="<?= e(asset('video/' . $p['video'])) ?>" type="video/mp4">
      </video>
      <?php endif; ?>
    </div>
    <?php if (count($p['imagens']) > 1 || !empty($p['video'])): ?>
    <ul class="galeria-miniaturas" aria-label="Outras fotos e vídeo">
      <?php foreach ($p['imagens'] as $i => $img):
        $base = 'produtos/' . $p['slug'] . '/' . pathinfo($img, PATHINFO_FILENAME); ?>
      <li>
        <button type="button" data-galeria-mini data-nota="<?= e(nota_imagem($img)) ?>" data-src="<?= e(asset('img/' . $base . '-1440.webp')) ?>" data-srcset="<?= e(asset('img/' . $base . '-960.webp')) ?> 960w, <?= e(asset('img/' . $base . '-1440.webp')) ?> 1440w"<?= $i === 0 ? ' aria-current="true"' : '' ?> aria-label="Ver foto <?= $i + 1 ?>">
          <?= imagem_produto($p, $i, ['sizes' => '96px', 'alt' => '']) ?>
        </button>
      </li>
      <?php endforeach; ?>
      <?php if (!empty($p['video'])): ?>
      <li>
        <button type="button" class="galeria-mini-video" data-galeria-mini data-video aria-label="Ver vídeo do produto">
          <img src="<?= e(asset('video/' . pathinfo($p['video'], PATHINFO_FILENAME) . '.jpg')) ?>" alt="" loading="lazy" width="96" height="72">
          <span aria-hidden="true"><?= icone('seta') ?></span>
        </button>
      </li>
      <?php endif; ?>
    </ul>
    <?php endif; ?>
  </div>

  <div class="produto-info">
    <a class="produto-categoria" href="<?= e(url('/' . $cat['slug'] . '/' . $sub['slug'] . '/')) ?>"><?= e($sub['nome']) ?></a>
    <h1 class="produto-nome"><?= e($p['nome']) ?></h1>
    <?php if (!empty($p['resumo'])): ?><p class="produto-resumo"><?= e($p['resumo']) ?></p><?php endif; ?>

    <div class="produto-preco" data-preco-base="<?= (float) $p['preco'] ?>" data-parcela-base="<?= e(texto_parcela($p)) ?>" data-parcelas="<?= $qtdParcelas ?>">
      <?php partial('etiqueta', ['p' => $p, 'tamanho' => 'g']); ?>
      <?php if (pronta_entrega($p)): ?>
      <p class="produto-status"><?= icone('check') ?>Pronta-entrega em até 48h na Grande São Paulo</p>
      <?php else: ?>
      <p class="produto-status produto-status-encomenda"><?= icone('relogio') ?>Sob encomenda: entrega em até 30 dias úteis. Veja o mostruário na loja.</p>
      <?php endif; ?>
    </div>
    <?php if (!empty($p['nota_preco'])): ?><p class="produto-nota"><?= e($p['nota_preco']) ?></p><?php endif; ?>
    <?php if (!empty($p['observacao'])): ?><p class="produto-nota produto-nota-destaque"><?= e($p['observacao']) ?></p><?php endif; ?>

    <?php foreach ($opcoes as $g => $grupo): ?>
    <fieldset class="produto-variacoes">
      <legend><?= e($grupo['nome']) ?>:</legend>
      <div class="variacoes-lista">
        <?php foreach ($grupo['valores'] as $i => $v): ?>
        <label class="variacao"><input type="radio" name="opcao-<?= $g ?>" value="<?= e($v) ?>" data-variacao="<?= $g ?>"<?= $grupo['nome'] === 'Tamanho' ? ' data-preco="' . ($precosTamanho[$i] ?? '') . '" data-parcela="' . ($precosTamanho[$i] === null ? '' : e(texto_parcela($p, $precosTamanho[$i]))) . '"' : '' ?><?= $i === ($grupo['padrao'] ?? 0) ? ' checked' : '' ?>><span><?= e($v) ?></span></label>
        <?php endforeach; ?>
      </div>
    </fieldset>
    <?php endforeach; ?>

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
    <strong data-barra-valor>R$ <?= preco((float) $p['preco']) ?></strong>
    <span data-barra-parcela><?= e(texto_parcela($p)) ?></span>
  </div>
  <a class="btn btn-primario" <?= $atributosPedido ?> data-local="barra"><?= icone('whatsapp') ?>Pedir pelo WhatsApp</a>
</div>
