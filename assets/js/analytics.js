/* ==========================================================================
   BHD Móveis — medição (Google Tag Manager, GA4, Meta Pixel e Microsoft Clarity)

   - Os IDs ficam em data/site.json → analytics. Com "ativo": false, nada é carregado.
   - Google (Tag Manager / GA4) carrega sempre, no Modo de Consentimento: sem aceite, ele não grava
     cookies e só envia sinais anônimos; com aceite, passa a medir completo.
   - Clarity e Meta Pixel só carregam depois que a pessoa aceita os cookies.
   - Todo clique em um elemento com data-track="evento" é registrado.
     Ex.: <a data-track="click_whatsapp" data-local="header">
   - Clique em "Pedir pelo WhatsApp" é registrado como interesse (Lead), não como venda.
   - Com o Tag Manager, cada evento também vai para o dataLayer ({ event: 'click_pedido_whatsapp', ... }):
     no GTM, use um acionador "Evento personalizado" com o mesmo nome.
   - O link "Cookies" do rodapé (data-cookies) reabre a escolha.
   ========================================================================== */
(function () {
  'use strict';

  var BHD = window.BHD || (window.BHD = {});
  var cfg = BHD.analytics || {};
  var temGoogle = !!(cfg.gtm || cfg.ga4);
  var temRastreio = !!(temGoogle || cfg.pixel || cfg.clarity);
  var CHAVE = 'bhd-consentimento';
  var extrasCarregados = false;

  // Eventos do site → eventos padrão do Meta Pixel
  var EVENTOS_PIXEL = { view_item: 'ViewContent', click_whatsapp: 'Contact', click_pedido_whatsapp: 'Lead', search: 'Search' };

  function ler() { try { return localStorage.getItem(CHAVE); } catch (e) { return null; } }
  function gravar(v) { try { localStorage.setItem(CHAVE, v); } catch (e) { /* navegação privada */ } }

  function script(src) {
    var s = document.createElement('script');
    s.async = true;
    s.src = src;
    document.head.appendChild(s);
  }

  window.dataLayer = window.dataLayer || [];
  window.gtag = window.gtag || function () { window.dataLayer.push(arguments); };

  function rastrear(evento, dados) {
    dados = dados || {};
    window.dataLayer.push(Object.assign({ event: evento }, dados));
    if (cfg.ga4) window.gtag('event', evento, dados);
    if (!extrasCarregados) return;
    if (cfg.pixel && window.fbq) {
      if (EVENTOS_PIXEL[evento]) window.fbq('track', EVENTOS_PIXEL[evento], dados);
      else window.fbq('trackCustom', evento, dados);
    }
    if (cfg.clarity && window.clarity) window.clarity('event', evento);
  }

  /* --- Google: sempre, começando sem cookies até a pessoa aceitar --- */
  function iniciarGoogle(aceito) {
    var estado = aceito ? 'granted' : 'denied';
    window.gtag('consent', 'default', {
      analytics_storage: estado,
      ad_storage: estado,
      ad_user_data: estado,
      ad_personalization: estado,
      wait_for_update: 500
    });
    if (cfg.gtm) {
      /* Google Tag Manager (código oficial) */
      window.dataLayer.push({ 'gtm.start': new Date().getTime(), event: 'gtm.js' });
      script('https://www.googletagmanager.com/gtm.js?id=' + encodeURIComponent(cfg.gtm));
    }
    if (cfg.ga4) {
      window.gtag('js', new Date());
      window.gtag('config', cfg.ga4);
      script('https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(cfg.ga4));
    }
  }

  /* --- Depois do aceite: libera os cookies do Google e carrega Clarity e Pixel --- */
  function aceitar() {
    if (temGoogle) {
      window.gtag('consent', 'update', {
        analytics_storage: 'granted', ad_storage: 'granted', ad_user_data: 'granted', ad_personalization: 'granted'
      });
    }
    if (extrasCarregados) return;
    extrasCarregados = true;

    if (cfg.pixel) {
      /* Meta Pixel (código oficial) */
      !function (f, b, e, v, n, t, s) {
        if (f.fbq) return; n = f.fbq = function () { n.callMethod ? n.callMethod.apply(n, arguments) : n.queue.push(arguments); };
        if (!f._fbq) f._fbq = n; n.push = n; n.loaded = !0; n.version = '2.0'; n.queue = [];
        t = b.createElement(e); t.async = !0; t.src = v; s = b.getElementsByTagName(e)[0]; s.parentNode.insertBefore(t, s);
      }(window, document, 'script', 'https://connect.facebook.net/en_US/fbevents.js');
      window.fbq('init', cfg.pixel);
      window.fbq('track', 'PageView');
    }

    if (cfg.clarity) {
      /* Microsoft Clarity (código oficial) */
      (function (c, l, a, r, i, t, y) {
        c[a] = c[a] || function () { (c[a].q = c[a].q || []).push(arguments); };
        t = l.createElement(r); t.async = 1; t.src = 'https://www.clarity.ms/tag/' + i;
        y = l.getElementsByTagName(r)[0]; y.parentNode.insertBefore(t, y);
      })(window, document, 'clarity', 'script', cfg.clarity);
      window.clarity('consent');
    }
  }

  /* --- Recusa depois de ter aceitado (pelo link do rodapé): o Google volta a não gravar cookies --- */
  function recusar() {
    if (temGoogle) {
      window.gtag('consent', 'update', {
        analytics_storage: 'denied', ad_storage: 'denied', ad_user_data: 'denied', ad_personalization: 'denied'
      });
    }
  }

  BHD.rastrear = rastrear;

  document.addEventListener('click', function (e) {
    var el = e.target.closest('[data-track]');
    if (!el) return;
    rastrear(el.getAttribute('data-track'), {
      local: el.getAttribute('data-local') || '',
      item_id: el.getAttribute('data-item') || '',
      link_url: el.href || ''
    });
  });

  if (!temRastreio) return;

  var escolha = ler();
  if (temGoogle) iniciarGoogle(escolha === 'aceito');
  if (escolha === 'aceito') aceitar();

  if (BHD.produto) {
    rastrear('view_item', {
      item_id: BHD.produto.id,
      item_name: BHD.produto.nome,
      item_category: BHD.produto.categoria,
      value: BHD.produto.preco,
      currency: 'BRL'
    });
  }

  /* --- Barra de cookies (uma linha, no pé da tela) --- */
  document.addEventListener('DOMContentLoaded', function () {
    var aviso = document.getElementById('consentimento');
    if (!aviso) return;
    var mostrar = function (sim) {
      aviso.hidden = !sim;
      document.documentElement.classList.toggle('com-consentimento', sim);
    };
    if (escolha !== 'aceito' && escolha !== 'recusado') mostrar(true);

    aviso.addEventListener('click', function (e) {
      var botao = e.target.closest('[data-consentimento]');
      if (!botao) return;
      var aceitou = botao.getAttribute('data-consentimento') === 'aceitar';
      gravar(aceitou ? 'aceito' : 'recusado');
      mostrar(false);
      if (aceitou) aceitar(); else recusar();
    });

    document.addEventListener('click', function (e) {
      if (!e.target.closest('[data-cookies]')) return;
      e.preventDefault();
      mostrar(true);
    });
  });
})();
