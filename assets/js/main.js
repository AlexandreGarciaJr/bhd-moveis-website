/* ==========================================================================
   BHD Móveis — interface
   Menu, busca, carrossel, ordenação/filtro, galeria e aviso de avaliação.
   Tudo funciona sem as animações (motion.js é opcional).
   ========================================================================== */
(function () {
  'use strict';

  var BHD = window.BHD || (window.BHD = {});
  var $ = function (sel, ctx) { return (ctx || document).querySelector(sel); };
  var $$ = function (sel, ctx) { return Array.prototype.slice.call((ctx || document).querySelectorAll(sel)); };

  function armazenar(chave, valor) { try { localStorage.setItem(chave, valor); } catch (e) { /* ignora */ } }
  function lerArmazenado(chave) { try { return localStorage.getItem(chave); } catch (e) { return null; } }

  /* ---------- Diálogos (menu e busca) ---------- */
  function abrir(id) {
    var d = document.getElementById(id);
    if (!d || d.open) return;
    $$('dialog[open]').forEach(function (o) { o.close(); });
    d.showModal();
    if (id === 'busca') iniciarBusca();
  }

  document.addEventListener('click', function (e) {
    var gatilho = e.target.closest('[data-abrir]');
    if (gatilho) { abrir(gatilho.getAttribute('data-abrir')); return; }
    var fechar = e.target.closest('[data-fechar]');
    if (fechar) { fechar.closest('dialog').close(); return; }
    if (e.target.tagName === 'DIALOG') e.target.close(); // clique no fundo
  });

  /* ---------- Busca ---------- */
  var indice = null;
  var campo = $('#busca-campo');
  var normalizar = function (t) { return (t || '').normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase(); };
  var esc = function (t) { return String(t).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); };
  var temporizadorBusca;

  function iniciarBusca() {
    if (!campo) return;
    setTimeout(function () { campo.focus(); }, 30);
    if (indice) return;
    fetch(campo.getAttribute('data-busca-url'))
      .then(function (r) { return r.json(); })
      .then(function (dados) {
        indice = dados.map(function (p) { p._t = normalizar(p.n + ' ' + p.c + ' ' + p.r); return p; });
        buscar();
      })
      .catch(function () { $('.busca-status').textContent = 'Não foi possível carregar a busca agora.'; });
  }

  function buscar() {
    if (!indice) return;
    var termo = normalizar(campo.value.trim());
    var lista = $('.busca-resultados');
    var status = $('.busca-status');
    if (!termo) { lista.innerHTML = ''; status.textContent = ''; return; }
    var partes = termo.split(/\s+/);
    var achados = indice.filter(function (p) { return partes.every(function (t) { return p._t.indexOf(t) > -1; }); }).slice(0, 8);
    status.textContent = achados.length
      ? achados.length + (achados.length === 1 ? ' produto encontrado' : ' produtos encontrados')
      : 'Nenhum produto encontrado para "' + campo.value.trim() + '". Tente outra palavra ou chame no WhatsApp.';
    lista.innerHTML = achados.map(function (p) {
      return '<li><a href="' + esc(p.u) + '"><img src="' + esc(p.i) + '" alt="" loading="lazy" width="56" height="64">' +
        '<span><b>' + esc(p.n) + '</b><span>' + esc(p.c) + '</span></span><em>R$ ' + esc(p.p) + '</em></a></li>';
    }).join('');
    clearTimeout(temporizadorBusca);
    temporizadorBusca = setTimeout(function () { if (BHD.rastrear) BHD.rastrear('search', { search_term: campo.value.trim() }); }, 900);
  }
  if (campo) {
    campo.addEventListener('input', buscar);
    // no campo de busca o Esc só limparia o texto; aqui ele fecha a janela
    campo.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') { e.preventDefault(); campo.closest('dialog').close(); }
    });
  }

  /* ---------- Trilho (carrossel) ---------- */
  $$('[data-trilho]').forEach(function (trilho) {
    var secao = trilho.closest('section') || document;
    var anterior = $('[data-trilho-anterior]', secao);
    var proximo = $('[data-trilho-proximo]', secao);
    var barra = $('[data-trilho-barra]', secao);

    function atualizar() {
      var max = trilho.scrollWidth - trilho.clientWidth;
      var x = trilho.scrollLeft;
      if (anterior) anterior.disabled = x <= 4;
      if (proximo) proximo.disabled = x >= max - 4;
      if (barra) barra.style.transform = 'scaleX(' + Math.min(1, (x + trilho.clientWidth) / trilho.scrollWidth) + ')';
    }
    function mover(dir) { trilho.scrollBy({ left: dir * trilho.clientWidth * 0.8, behavior: 'smooth' }); }

    if (anterior) anterior.addEventListener('click', function () { mover(-1); });
    if (proximo) proximo.addEventListener('click', function () { mover(1); });
    trilho.addEventListener('scroll', function () { requestAnimationFrame(atualizar); }, { passive: true });
    window.addEventListener('resize', atualizar);
    trilho.addEventListener('keydown', function (e) {
      if (e.key === 'ArrowRight') { e.preventDefault(); mover(1); }
      if (e.key === 'ArrowLeft') { e.preventDefault(); mover(-1); }
    });
    atualizar();
  });

  /* ---------- Ordenação e filtro das listas ---------- */
  var lista = $('[data-lista]');
  if (lista) {
    var cards = $$('[data-card]', lista);
    cards.forEach(function (c, i) { c.dataset.ordem = i; });
    var ordenar = $('[data-ordenar]');
    var filtro = $('[data-filtro-pronta]');
    var total = $('[data-lista-total]');
    var vazia = $('[data-lista-vazia]');
    var num = function (c, k) { return parseFloat(c.dataset[k]) || 0; };
    var criterios = {
      destaque: function (a, b) { return num(b, 'destaque') - num(a, 'destaque') || num(a, 'ordem') - num(b, 'ordem'); },
      menor: function (a, b) { return num(a, 'preco') - num(b, 'preco'); },
      maior: function (a, b) { return num(b, 'preco') - num(a, 'preco'); },
      desconto: function (a, b) { return num(b, 'desconto') - num(a, 'desconto') || num(a, 'ordem') - num(b, 'ordem'); }
    };

    function aplicar() {
      var modo = ordenar ? ordenar.value : 'destaque';
      cards.sort(criterios[modo] || criterios.destaque).forEach(function (c) { lista.appendChild(c); });
      var visiveis = 0;
      cards.forEach(function (c) {
        var mostrar = !(filtro && filtro.checked) || c.dataset.pronta === '1';
        c.hidden = !mostrar;
        if (mostrar) visiveis++;
      });
      if (total) total.textContent = visiveis;
      if (vazia) vazia.hidden = visiveis > 0;
    }
    if (ordenar) ordenar.addEventListener('change', aplicar);
    if (filtro) filtro.addEventListener('change', aplicar);
    $$('[data-limpar-filtro]').forEach(function (b) { b.addEventListener('click', function () { filtro.checked = false; aplicar(); }); });
  }

  /* ---------- Galeria do produto ---------- */
  var principal = $('[data-galeria-principal]');
  if (principal) {
    $$('[data-galeria-mini]').forEach(function (botao) {
      botao.addEventListener('click', function () {
        if (botao.getAttribute('aria-current') === 'true') return;
        var trocar = function () {
          principal.srcset = botao.getAttribute('data-srcset');
          principal.src = botao.getAttribute('data-src');
          $$('[data-galeria-mini]').forEach(function (b) { b.removeAttribute('aria-current'); });
          botao.setAttribute('aria-current', 'true');
        };
        if (BHD.animarGaleria) BHD.animarGaleria(botao, principal, trocar);
        else trocar();
      });
    });
  }

  /* ---------- Cenas do hero ---------- */
  var midia = $('[data-cenas]');
  if (midia) {
    var cenas = $$('[data-cena]', midia);
    var botoesCena = $$('[data-ir-cena]', midia);
    var atual = 0;

    // Desenha o cordão da âncora (no móvel) até o furo da etiqueta, em % da cena
    var ajustarCordao = function (cena) {
      var path = $('.hero-cordao path', cena);
      var tag = $('.hero-etiqueta', cena);
      var etiqueta = $('.etiqueta', cena);
      if (!path || !tag || !etiqueta || !cena.offsetWidth) return;
      var cs = getComputedStyle(cena);
      var ax = parseFloat(cs.getPropertyValue('--ax'));
      var ay = parseFloat(cs.getPropertyValue('--ay'));
      var hx = (tag.offsetLeft + 13) / cena.offsetWidth * 100;
      var hy = (tag.offsetTop + etiqueta.offsetHeight / 2) / cena.offsetHeight * 100;
      var cx = (ax + hx) / 2;
      var cy = (ay + hy) / 2 + 4; // leve barriga no cordão
      path.setAttribute('d', 'M' + ax + ' ' + ay + ' Q ' + cx.toFixed(2) + ' ' + cy.toFixed(2) + ' ' + hx.toFixed(2) + ' ' + hy.toFixed(2));
    };
    var ajustarTodos = function () { cenas.forEach(ajustarCordao); };

    var marcar = function (i) {
      botoesCena.forEach(function (b, k) {
        if (k === i) b.setAttribute('aria-current', 'true');
        else b.removeAttribute('aria-current');
      });
    };

    // Troca sem animação (sem JS de motion ou com "reduzir movimento")
    var mostrar = function (i) {
      cenas.forEach(function (c, k) {
        var ativa = k === i;
        c.classList.toggle('ativa', ativa);
        if (ativa) { c.removeAttribute('aria-hidden'); c.inert = false; }
        else { c.setAttribute('aria-hidden', 'true'); c.inert = true; }
      });
      atual = i;
      marcar(i);
    };

    BHD.cenas = {
      midia: midia,
      lista: cenas,
      atual: function () { return atual; },
      definirAtual: function (i) { atual = i; marcar(i); },
      mostrar: mostrar,
      ajustarCordao: ajustarCordao
    };

    botoesCena.forEach(function (b) {
      b.addEventListener('click', function () {
        var i = parseInt(b.getAttribute('data-ir-cena'), 10);
        if (BHD.irParaCena) BHD.irParaCena(i);
        else mostrar(i);
      });
    });

    ajustarTodos();
    window.addEventListener('resize', ajustarTodos);
    if (document.fonts && document.fonts.ready) document.fonts.ready.then(ajustarTodos);
  }

  /* ---------- Aviso de avaliação do Google ---------- */
  var aviso = $('#aviso-avaliacao');
  var cfgAviso = BHD.popup || {};
  if (aviso && cfgAviso.ativo) {
    var CHAVE_AVISO = 'bhd-aviso-avaliacao';
    var visto = parseInt(lerArmazenado(CHAVE_AVISO) || '0', 10);
    var dias = (cfgAviso.dias_para_reaparecer || 30) * 864e5;
    var dispensar = function () { aviso.hidden = true; armazenar(CHAVE_AVISO, String(Date.now())); };

    if (Date.now() - visto > dias) {
      setTimeout(function () {
        // não aparece junto com o aviso de cookies nem com um menu aberto
        if (document.documentElement.classList.contains('com-consentimento') || $('dialog[open]')) return;
        aviso.hidden = false;
      }, (cfgAviso.atraso_segundos || 30) * 1000);
    }
    $('[data-fechar-aviso]', aviso).addEventListener('click', dispensar);
    $$('a', aviso).forEach(function (a) { a.addEventListener('click', dispensar); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !aviso.hidden) dispensar(); });
  }
})();
