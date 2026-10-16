<?php $faq = bhd('site')['faq']; ?>
<section class="secao faq" aria-labelledby="faq-titulo">
  <div class="faq-intro">
    <h2 class="titulo-secao" id="faq-titulo">Perguntas frequentes</h2>
    <p>Não achou sua dúvida? Fale com a gente.</p>
    <a class="btn btn-contorno" href="<?= e(whatsapp('Olá! Tenho uma dúvida sobre os móveis da BHD.')) ?>" target="_blank" rel="noopener" data-track="click_whatsapp" data-local="faq"><?= icone('whatsapp') ?>Perguntar no WhatsApp</a>
  </div>
  <div class="faq-lista">
    <?php foreach ($faq as $i => $item): ?>
    <details class="faq-item"<?= $i === 0 ? ' open' : '' ?>>
      <summary><?= e($item['pergunta']) ?><?= icone('mais') ?></summary>
      <p><?= e($item['resposta']) ?></p>
    </details>
    <?php endforeach; ?>
  </div>
</section>
<?= json_ld([
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => array_map(fn($f) => ['@type' => 'Question', 'name' => $f['pergunta'], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['resposta']]], array_values(array_filter($faq, fn($f) => !str_contains($f['resposta'], 'a confirmar')))),
]) ?>
