<?php
$site = bhd('site');
$a = $site['analytics'];
$temRastreio = !empty($a['gtm']['ativo']) || $a['ga4']['ativo'] || $a['meta_pixel']['ativo'] || $a['clarity']['ativo'];
$popup = $site['avaliacoes']['popup']['ativo'] && empty(bhd('pagina')['produto']);
?>
<a class="whats-flutuante" href="<?= e(whatsapp()) ?>" target="_blank" rel="noopener" data-track="click_whatsapp" data-local="flutuante">
  <?= icone('whatsapp') ?><span>Fale no WhatsApp</span>
</a>

<?php if ($popup): ?>
<aside class="aviso-avaliacao" id="aviso-avaliacao" aria-labelledby="aviso-avaliacao-titulo" hidden>
  <button class="btn-icone btn-icone-pequeno" type="button" data-fechar-aviso aria-label="Fechar aviso"><?= icone('fechar') ?></button>
  <p class="aviso-avaliacao-titulo" id="aviso-avaliacao-titulo"><?= icone('estrela') ?>Já comprou na BHD?</p>
  <p>Sua avaliação no Google ajuda outras pessoas a conhecerem a loja.</p>
  <div class="aviso-avaliacao-acoes">
    <?php foreach (bhd('unidades') as $u): ?>
    <a href="<?= e($u['avaliar']) ?>" target="_blank" rel="noopener" data-track="click_avaliacao" data-local="<?= e($u['slug']) ?>">Avaliar <?= e(preg_replace('/^BHD\s+/', '', $u['nome'])) ?></a>
    <?php endforeach; ?>
  </div>
</aside>
<?php endif; ?>

<?php if ($temRastreio): ?>
<div class="consentimento" id="consentimento" role="region" aria-label="Aviso de cookies" hidden>
  <p>Usamos cookies<span class="consentimento-extra"> para medir as visitas e melhorar o site</span>. <a href="<?= e(url('/privacidade/')) ?>">Saiba mais</a></p>
  <button class="consentimento-recusar" type="button" data-consentimento="recusar">Recusar</button>
  <button class="consentimento-aceitar" type="button" data-consentimento="aceitar">Aceitar</button>
</div>
<?php endif; ?>
