<?php $site = bhd('site'); ?>
<article class="texto-longo">
  <h1 class="titulo-pagina">Política de privacidade</h1>
  <?php if ($site['demo']): ?><p class="nota-provisoria">Modelo provisório. Deve ser revisado pela BHD (e, se possível, por um profissional jurídico) antes da publicação.</p><?php endif; ?>

  <p>Esta política explica como a <?= e($site['nome_completo']) ?> trata as informações de quem visita este site, conforme a Lei Geral de Proteção de Dados (Lei nº 13.709/2018).</p>

  <h2>Quais dados coletamos</h2>
  <p>Este site não tem cadastro, login nem pagamento. Não pedimos nome, CPF ou dados de cartão.</p>
  <p>Se você aceitar os cookies de medição, coletamos dados de navegação de forma agregada: páginas visitadas, tempo de visita, tipo de dispositivo, cidade aproximada e cliques em botões (como WhatsApp, Mercado Livre e Amazon).</p>

  <h2>Ferramentas de medição</h2>
  <p>Podemos usar o Google Analytics, o Pixel da Meta e o Microsoft Clarity para entender como o site é usado e para medir nossos anúncios. Essas ferramentas só são carregadas depois que você aceita o aviso de cookies.</p>

  <h2>Links para outros sites</h2>
  <p>Ao clicar em WhatsApp, Mercado Livre, Amazon ou Google Maps, você sai deste site. Esses serviços têm políticas de privacidade próprias.</p>

  <h2>Seus direitos</h2>
  <p>Você pode recusar os cookies a qualquer momento limpando os dados do site no seu navegador. Para pedir informações ou a exclusão de dados, fale com a gente em <a href="mailto:<?= e($site['contato']['email']) ?>"><?= e($site['contato']['email']) ?></a>.</p>

  <p class="texto-atualizado">Última atualização: <?= date('d/m/Y') ?>.</p>
</article>
