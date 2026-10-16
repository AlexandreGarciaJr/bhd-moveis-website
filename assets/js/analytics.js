/* ==========================================================================
   BHD Móveis — medição (GA4, Meta Pixel e Microsoft Clarity)

   - Os IDs ficam em data/site.json → analytics. Com "ativo": false, nada é carregado.
   - As ferramentas só carregam depois que a pessoa aceita o aviso de cookies.
   - Todo clique em um elemento com data-track="evento" é registrado.
     Ex.: <a data-track="click_whatsapp" data-local="header">
   - Clique em Mercado Livre/Amazon é registrado como interesse, não como venda.
   ========================================================================== */
(function () {
  'use strict';

  var BHD = window.BHD || (window.BHD = {});
  var cfg = BHD.analytics || {};
  var temRastreio = !!(cfg.ga4 || cfg.pixel || cfg.clarity);
  var CHAVE = 'bhd-consentimento';
  var carregado = false;

  // Eventos do site → eventos padrão do Meta Pixel
  var EVENTOS_PIXEL = { view_item: 'ViewContent', click_whatsapp: 'Contact', search: 'Search' };

  function ler() { try { return localStorage.getItem(CHAVE); } catch (e) { return null; } }
  function gravar(v) { try { localStorage.setItem(CHAVE, v); } catch (e) { /* navegação privada */ } }

  function script(src) {
    var s = document.createElement('script');
    s.async = true;
    s.src = src;
    document.head.appendChild(s);
  }

  function rastrear(evento, dados) {
    dados = dados || {};
    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push(Object.assign({ event: evento }, dados));
    if (!carregado) return;
    if (cfg.ga4 && window.gtag) window.gtag('event', evento, dados);
    if (cfg.pixel && window.fbq) {
      if (EVENTOS_PIXEL[evento]) window.fbq('track', EVENTOS_PIXEL[evento], dados);
      else window.fbq('trackCustom', evento, dados);
    }
    if (cfg.clarity && window.clarity) window.clarity('event', evento);
  }

  function carregar() {
    if (carregado) return;
    carregado = true;

    if (cfg.ga4) {
      window.dataLayer = window.dataLayer || [];
      window.gtag = function () { window.dataLayer.push(arguments); };
      window.gtag('js', new Date());
      window.gtag('config', cfg.ga4);
      script('https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(cfg.ga4));
    }

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
    }

    if (BHD.produto) {
      rastrear('view_item', {
        item_id: BHD.produto.id,
        item_name: BHD.produto.nome,
        item_category: BHD.produto.categoria,
        value: BHD.produto.preco,
        currency: 'BRL'
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
  if (escolha === 'aceito') { carregar(); return; }
  if (escolha === 'recusado') return;

  document.addEventListener('DOMContentLoaded', function () {
    var aviso = document.getElementById('consentimento');
    if (!aviso) return;
    aviso.hidden = false;
    document.documentElement.classList.add('com-consentimento');
    aviso.addEventListener('click', function (e) {
      var botao = e.target.closest('[data-consentimento]');
      if (!botao) return;
      var aceitou = botao.getAttribute('data-consentimento') === 'aceitar';
      gravar(aceitou ? 'aceito' : 'recusado');
      aviso.hidden = true;
      document.documentElement.classList.remove('com-consentimento');
      if (aceitou) carregar();
    });
  });
})();
