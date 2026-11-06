<?php $site = bhd('site'); ?>
<article class="texto-longo">
  <h1 class="titulo-pagina">Política de privacidade</h1>
  <?php if ($site['demo']): ?><p class="nota-provisoria">Modelo provisório. Deve ser revisado pela BHD (e, se possível, por um profissional jurídico) antes da publicação.</p><?php endif; ?>

  <p>Esta política explica como a <?= e($site['nome_completo']) ?> trata as informações de quem visita este site, conforme a Lei Geral de Proteção de Dados (Lei nº 13.709/2018).</p>

  <h2>Quais dados coletamos</h2>
  <p>Este site não tem cadastro, login nem pagamento. Não pedimos nome, CPF ou dados de cartão.</p>
  <p>Medimos as visitas de forma agregada: páginas visitadas, tempo de visita, tipo de dispositivo, cidade aproximada e cliques em botões (como WhatsApp e rotas).</p>

  <h2>Ferramentas de medição</h2>
  <p>Usamos o Google Analytics (pelo Google Tag Manager) no modo de consentimento: enquanto você não aceita os cookies, ele não grava cookies no seu navegador e envia apenas sinais anônimos de visita. O Microsoft Clarity, que registra como as páginas são usadas (cliques e rolagem), e o Pixel da Meta, se estiver ativo, só são carregados depois que você aceita.</p>

  <h2>Links para outros sites</h2>
  <p>Ao clicar em WhatsApp, Instagram ou Google Maps, você sai deste site. Esses serviços têm políticas de privacidade próprias.</p>

  <h2>Seus direitos</h2>
  <p>Você pode mudar sua escolha a qualquer momento pelo link “Cookies” no rodapé do site, ou limpando os dados do site no seu navegador. Para pedir informações ou a exclusão de dados, fale com a gente em <a href="mailto:<?= e($site['contato']['email']) ?>"><?= e($site['contato']['email']) ?></a>.</p>

  <p class="texto-atualizado">Última atualização: <?= date('d/m/Y') ?>.</p>
</article>
