/* ==========================================================================
   BHD Móveis — motion (GSAP + ScrollTrigger + Flip)

   Momentos:
   1. Entrada do hero: cortina abre a foto, título sobe linha a linha, etiqueta cai e balança.
   2. Cenas do hero: a cada N segundos um novo ambiente se abre em círculo a partir da
      âncora da etiqueta; a etiqueta antiga cai e a nova se pendura.
   3. Saída do hero: as letras do título se desfazem, a foto encolhe numa janela e a
      etiqueta cai enquanto as categorias sobem por cima.
   4. Faixa de texto que anda com o scroll.
   5. Cursor contextual, botões magnéticos e balanço da etiqueta nos cards (desktop).
   6. Galeria do produto: a miniatura "voa" até a foto principal.

   Sem JavaScript, sem GSAP ou com "reduzir movimento", o site funciona sem animações.
   ========================================================================== */
(function () {
  'use strict';

  var raiz = document.documentElement;
  var gsap = window.gsap;
  if (!gsap || !raiz.classList.contains('motion')) {
    raiz.classList.remove('motion');
    return;
  }
  raiz.classList.add('motion-pronto');
  ['ScrollTrigger', 'Flip'].forEach(function (p) { if (window[p]) gsap.registerPlugin(window[p]); });

  var BHD = window.BHD || (window.BHD = {});
  var mm = gsap.matchMedia();
  var MOUSE = '(hover: hover) and (pointer: fine)';
  var HERO_FIXO = '(min-width: 900px) and (min-height: 640px)';
  var $ = function (s, c) { return (c || document).querySelector(s); };
  var $$ = function (s, c) { return Array.prototype.slice.call((c || document).querySelectorAll(s)); };

  /* ======================================================================
     HERO
     ====================================================================== */
  var hero = $('.hero');
  var cenasApi = BHD.cenas;

  if (hero) {
    /* --- Divide o título em letras (para a saída) --- */
    var titulo = $('.hero-titulo', hero);
    titulo.setAttribute('aria-label', titulo.textContent.replace(/\s+/g, ' ').trim());
    $$('.hero-linha > span', titulo).forEach(function (linha) {
      linha.setAttribute('aria-hidden', 'true');
      linha.innerHTML = linha.textContent.split(' ').map(function (palavra) {
        return '<span class="palavra">' + palavra.split('').map(function (c) {
          return '<span class="letra">' + c + '</span>';
        }).join('') + '</span>';
      }).join(' ');
    });
    var letras = $$('.letra', titulo);

    /* --- 1. Entrada --- */
    var cenaInicial = $('.hero-cena.ativa', hero);
    var entrada = gsap.timeline({ defaults: { ease: 'power3.out' } })
      .to('.hero-cortina', { scaleX: 0, duration: 1.05, ease: 'power4.inOut' }, 0)
      .fromTo($('.hero-foto img', cenaInicial), { scale: 1.16 }, { scale: 1, duration: 1.8 }, 0)
      .to('.hero-linha > span', { y: 0, duration: .9, stagger: .08 }, .12)
      .fromTo('[data-hero-anim]', { opacity: 0, y: 14 }, { opacity: 1, y: 0, duration: .6, stagger: .08 }, .5)
      .to($$('.hero-cordao, .hero-ancora', cenaInicial), { opacity: 1, duration: .35 }, .9)
      .fromTo($('.hero-etiqueta', cenaInicial),
        { opacity: 0, y: -70, rotation: -16 },
        { opacity: 1, y: 0, rotation: 0, duration: 1.5, ease: 'elastic.out(1, 0.35)' }, .85);

    /* --- 2. Rotação das cenas --- */
    if (cenasApi && cenasApi.lista.length > 1) {
      var cenas = cenasApi.lista;
      var midia = cenasApi.midia;
      var segundos = parseFloat(midia.getAttribute('data-segundos')) || 6;
      var ocupado = false;
      var pausas = {};
      var relogio = null;

      // as cenas que ainda não apareceram começam sem etiqueta/cordão
      cenas.forEach(function (c) {
        if (c === cenaInicial) return;
        gsap.set($$('.hero-cordao, .hero-ancora, .hero-etiqueta', c), { opacity: 0 });
      });

      var barra = function (i) { return $('[data-ir-cena="' + i + '"] i', midia); };

      var iniciarRelogio = function () {
        if (relogio) relogio.kill();
        $$('.hero-indicadores i', midia).forEach(function (el) { gsap.set(el, { scaleX: 0 }); });
        relogio = gsap.fromTo(barra(cenasApi.atual()), { scaleX: 0 }, {
          scaleX: 1, duration: segundos, ease: 'none',
          onComplete: function () { avancar(); }
        });
        if (Object.keys(pausas).length) relogio.pause();
      };
      var pausar = function (motivo) { pausas[motivo] = 1; if (relogio) relogio.pause(); };
      var retomar = function (motivo) {
        delete pausas[motivo];
        if (relogio && !Object.keys(pausas).length && !ocupado) relogio.resume();
      };

      // se a transição anterior ainda está terminando, espera um instante
      var avancar = function () {
        if (ocupado) { gsap.delayedCall(.3, avancar); return; }
        irPara((cenasApi.atual() + 1) % cenas.length);
      };

      var irPara = function (para) {
        var de = cenasApi.atual();
        if (ocupado || para === de) return;
        ocupado = true;
        if (relogio) relogio.kill();

        var velha = cenas[de];
        var nova = cenas[para];
        var cs = getComputedStyle(nova);
        var origem = cs.getPropertyValue('--ax').trim() + ' ' + cs.getPropertyValue('--ay').trim();
        var tagVelha = $('.hero-etiqueta', velha);
        var tagNova = $('.hero-etiqueta', nova);
        var imgVelha = $('.hero-foto img', velha);

        cenasApi.ajustarCordao(nova);
        nova.classList.add('entrando');
        nova.removeAttribute('aria-hidden');
        nova.inert = false;
        cenasApi.definirAtual(para);
        $$('.hero-indicadores i', midia).forEach(function (el) { gsap.set(el, { scaleX: 0 }); });

        gsap.timeline({
          onComplete: function () {
            velha.classList.remove('ativa');
            velha.setAttribute('aria-hidden', 'true');
            velha.inert = true;
            nova.classList.remove('entrando');
            nova.classList.add('ativa');
            gsap.set(nova, { clearProps: 'clipPath' });
            gsap.set(imgVelha, { scale: 1 });
            gsap.set(tagVelha, { opacity: 0, y: 0, rotation: 0 });
            ocupado = false;
          }
        })
          .to(tagVelha, { rotation: 28, y: 90, opacity: 0, duration: .55, ease: 'power2.in' }, 0)
          .to($$('.hero-cordao, .hero-ancora', velha), { opacity: 0, duration: .3 }, 0)
          .to(imgVelha, { scale: 1.08, duration: 1.3, ease: 'power2.in' }, .2)
          .fromTo(nova, { clipPath: 'circle(0% at ' + origem + ')' },
            { clipPath: 'circle(150% at ' + origem + ')', duration: 1.3, ease: 'power3.inOut' }, .2)
          .fromTo($('.hero-foto img', nova), { scale: 1.25 }, { scale: 1, duration: 1.9, ease: 'power3.out' }, .2)
          .to($$('.hero-cordao, .hero-ancora', nova), { opacity: 1, duration: .3 }, 1.15)
          .fromTo(tagNova, { opacity: 0, y: -70, rotation: -16 },
            { opacity: 1, y: 0, rotation: 0, duration: 1.5, ease: 'elastic.out(1, 0.35)' }, 1.1)
          .call(iniciarRelogio, null, 1.5);
      };
      BHD.irParaCena = irPara;

      // pausa com o foco do teclado no hero, com a aba escondida ou com o hero fora da tela
      midia.addEventListener('focusin', function () { pausar('foco'); });
      midia.addEventListener('focusout', function () { retomar('foco'); });
      document.addEventListener('visibilitychange', function () {
        if (document.hidden) pausar('aba'); else retomar('aba');
      });
      if ('IntersectionObserver' in window) {
        new IntersectionObserver(function (e) {
          if (e[0].isIntersecting) retomar('fora'); else pausar('fora');
        }, { threshold: .25 }).observe(midia);
      }
      BHD.pausarCenas = pausar;
      BHD.retomarCenas = retomar;

      // a primeira contagem começa quando a etiqueta inicial termina de cair
      entrada.call(iniciarRelogio, null, 1.6);
    }

    /* --- 3. Saída do hero --- */
    if (window.ScrollTrigger && $('.folha')) {
      var aleatorio = gsap.utils.random;
      var desfazerLetras = function (tl) {
        tl.to(letras, {
          yPercent: function () { return aleatorio(-280, -120); },
          xPercent: function () { return aleatorio(-90, 90); },
          rotation: function () { return aleatorio(-60, 60); },
          opacity: 0,
          ease: 'power2.in',
          duration: .6,
          stagger: { each: .012, from: 'random' }
        }, 0);
      };
      var controlarPausa = function (self) {
        if (!BHD.pausarCenas) return;
        if (self.progress > .02) BHD.pausarCenas('saida'); else BHD.retomarCenas('saida');
      };

      mm.add(HERO_FIXO, function () {
        var tl = gsap.timeline({
          scrollTrigger: { trigger: '.folha', start: 'top bottom', end: 'top 12%', scrub: .8, onUpdate: controlarPausa }
        });
        desfazerLetras(tl);
        tl.to('.hero-apoio', { y: -70, opacity: 0, ease: 'power2.in', duration: .5 }, 0)
          .fromTo('.hero-midia',
            { clipPath: 'inset(0% 0% 0% 0% round 28px)' },
            { clipPath: 'inset(12% 9% 24% 16% round 28px)', scale: .94, rotation: -2.5, ease: 'power2.inOut', duration: 1 }, 0)
          .to('.hero-etiqueta .etiqueta', { rotation: 40, y: 240, opacity: 0, ease: 'power2.in', duration: .6 }, .05)
          .to('.hero-indicadores', { opacity: 0, duration: .3 }, 0)
          .to('.hero-escurecer', { opacity: .35, duration: 1 }, 0);
      });

      mm.add('(max-width: 899px)', function () {
        var tl = gsap.timeline({
          scrollTrigger: { trigger: '.hero', start: 'top top', end: 'bottom 35%', scrub: .6, onUpdate: controlarPausa }
        });
        desfazerLetras(tl);
        tl.to('.hero-apoio', { y: -40, opacity: 0, ease: 'power2.in', duration: .5 }, .1);
      });
    }
  }

  /* ======================================================================
     4. Faixa de texto e parallax do showroom
     ====================================================================== */
  if (window.ScrollTrigger) {
    $$('[data-faixa]').forEach(function (linha) {
      var dir = parseFloat(linha.getAttribute('data-faixa'));
      gsap.fromTo(linha, { xPercent: dir > 0 ? 0 : -30 }, {
        xPercent: dir > 0 ? -30 : 0,
        ease: 'none',
        scrollTrigger: { trigger: linha.parentNode, start: 'top bottom', end: 'bottom top', scrub: .5 }
      });
    });

    mm.add('(min-width: 900px)', function () {
      var img = $('.showroom-foto img');
      if (!img) return;
      gsap.fromTo(img, { scale: 1.18, yPercent: -6 }, {
        yPercent: 6, ease: 'none',
        scrollTrigger: { trigger: '.showroom-foto', start: 'top bottom', end: 'bottom top', scrub: true }
      });
    });
  }

  /* ======================================================================
     4b. Ofertas: a página para e a rolagem empurra o carrossel para o lado
     ====================================================================== */
  if (window.ScrollTrigger) {
    mm.add('(min-width: 900px) and (min-height: 600px)', function () {
      var secao = $('.ofertas');
      var trilho = secao && $('[data-trilho]', secao);
      if (!trilho) return;
      var barraProgresso = $('[data-trilho-barra]', secao);
      var anterior = $('[data-trilho-anterior]', secao);
      var proximo = $('[data-trilho-proximo]', secao);
      var distancia = function () { return Math.max(0, trilho.scrollWidth - trilho.clientWidth); };

      trilho.scrollLeft = 0;
      trilho.classList.add('trilho-fixo');

      var tween = gsap.to(trilho, {
        x: function () { return -distancia(); },
        ease: 'none',
        scrollTrigger: {
          trigger: secao,
          start: 'top top',
          end: function () { return '+=' + distancia(); },
          pin: true,
          scrub: .6,
          refreshPriority: 1, // calcula antes dos gatilhos que ficam abaixo
          invalidateOnRefresh: true,
          onUpdate: function (self) {
            var visivel = trilho.clientWidth / trilho.scrollWidth;
            if (barraProgresso) barraProgresso.style.transform = 'scaleX(' + (visivel + (1 - visivel) * self.progress) + ')';
            if (anterior) anterior.disabled = self.progress < .01;
            if (proximo) proximo.disabled = self.progress > .99;
          }
        }
      });
      var st = tween.scrollTrigger;

      // as setas rolam a página até o ponto equivalente do carrossel
      var setas = function (e) {
        var botao = e.target.closest('[data-trilho-anterior], [data-trilho-proximo]');
        if (!botao) return;
        e.stopImmediatePropagation();
        var passo = trilho.clientWidth * .8 / Math.max(distancia(), 1);
        var dir = botao.hasAttribute('data-trilho-proximo') ? 1 : -1;
        var alvo = Math.min(1, Math.max(0, st.progress + dir * passo));
        window.scrollTo({ top: st.start + (st.end - st.start) * alvo, behavior: 'smooth' });
      };
      secao.addEventListener('click', setas, true);

      // teclado: foco num card fora da área visível leva a página até ele
      var foco = function (e) {
        var card = e.target.closest('[data-card]');
        if (!card) return;
        var alvo = Math.min(1, card.offsetLeft / Math.max(distancia(), 1));
        window.scrollTo({ top: st.start + (st.end - st.start) * alvo });
      };
      trilho.addEventListener('focusin', foco);

      return function () {
        secao.removeEventListener('click', setas, true);
        trilho.removeEventListener('focusin', foco);
        trilho.classList.remove('trilho-fixo');
        gsap.set(trilho, { clearProps: 'x' });
      };
    });
  }

  /* ======================================================================
     5. Interações de mouse (desktop)
     ====================================================================== */
  mm.add(MOUSE, function () {
    var limpar = [];
    var ouvir = function (el, ev, fn, opc) { el.addEventListener(ev, fn, opc); limpar.push(function () { el.removeEventListener(ev, fn, opc); }); };

    /* --- Cursor contextual --- */
    var cursor = document.createElement('div');
    cursor.className = 'cursor';
    cursor.setAttribute('aria-hidden', 'true');
    cursor.innerHTML = '<span></span>';
    document.body.appendChild(cursor);
    var rotulo = cursor.firstChild;
    var cx = gsap.quickTo(cursor, 'x', { duration: .45, ease: 'power3' });
    var cy = gsap.quickTo(cursor, 'y', { duration: .45, ease: 'power3' });
    var visivel = false;
    // só sobre as fotos: em cima de texto o círculo atrapalharia a leitura
    var alvos = [
      ['.card-foto', 'Ver produto', ''],
      ['.categoria', 'Ver tudo', '']
    ];
    var mouseX = -100, mouseY = -100;
    var avaliar = function (alvo) {
      var achou = null;
      for (var i = 0; i < alvos.length; i++) {
        if (alvo && alvo.closest && alvo.closest(alvos[i][0])) { achou = alvos[i]; break; }
      }
      if (achou) {
        rotulo.textContent = achou[1];
        cursor.className = 'cursor ' + achou[2];
        if (!visivel) { visivel = true; gsap.to(cursor, { scale: 1, duration: .5, ease: 'expo.out', overwrite: 'auto' }); }
      } else if (visivel) {
        visivel = false;
        gsap.to(cursor, { scale: 0, duration: .35, ease: 'power3.out', overwrite: 'auto' });
      }
    };
    ouvir(window, 'pointermove', function (e) {
      mouseX = e.clientX; mouseY = e.clientY;
      cx(mouseX); cy(mouseY);
      avaliar(e.target);
    });
    // ao rolar sem mexer o mouse, o elemento embaixo do cursor muda
    ouvir(window, 'scroll', function () { avaliar(document.elementFromPoint(mouseX, mouseY)); }, { passive: true });
    ouvir(document, 'mouseleave', function () { visivel = false; gsap.to(cursor, { scale: 0, duration: .3, overwrite: 'auto' }); });
    limpar.push(function () { cursor.remove(); });

    /* --- Botões magnéticos --- */
    $$('[data-magnetico], .whats-flutuante, .trilho-setas .btn-icone, .topo-acoes .btn-icone, .topo-whats').forEach(function (el) {
      var mx = gsap.quickTo(el, 'x', { duration: .5, ease: 'power3' });
      var my = gsap.quickTo(el, 'y', { duration: .5, ease: 'power3' });
      ouvir(el, 'pointermove', function (e) {
        var r = el.getBoundingClientRect();
        mx((e.clientX - r.left - r.width / 2) * .3);
        my((e.clientY - r.top - r.height / 2) * .4);
      });
      ouvir(el, 'pointerleave', function () {
        gsap.to(el, { x: 0, y: 0, duration: .9, ease: 'elastic.out(1, 0.4)' });
      });
    });

    /* --- Balanço da etiqueta nos cards --- */
    $$('[data-card]').forEach(function (card) {
      var etiqueta = $('.card-foto .etiqueta', card);
      if (!etiqueta) return;
      ouvir(card, 'mouseenter', function () {
        gsap.killTweensOf(etiqueta);
        gsap.timeline()
          .to(etiqueta, { rotation: -8, y: -4, duration: .18, ease: 'power2.out' })
          .to(etiqueta, { rotation: 0, y: 0, duration: 1.3, ease: 'elastic.out(1.1, 0.28)' });
      });
    });

    /* --- Arraste do carrossel com inércia --- */
    $$('[data-trilho]').forEach(function (trilho) {
      var inicioX = 0, inicioScroll = 0, ultimoX = 0, ultimoT = 0, vel = 0, ativo = false, moveu = false;
      ouvir(trilho, 'pointerdown', function (e) {
        if (e.pointerType !== 'mouse' || e.button !== 0 || trilho.classList.contains('trilho-fixo')) return;
        ativo = true; moveu = false;
        inicioX = ultimoX = e.clientX; ultimoT = performance.now();
        inicioScroll = trilho.scrollLeft; vel = 0;
        gsap.killTweensOf(trilho);
      });
      ouvir(window, 'pointermove', function (e) {
        if (!ativo) return;
        var dx = e.clientX - inicioX;
        if (!moveu && Math.abs(dx) < 8) return;
        if (!moveu) { moveu = true; trilho.classList.add('arrastando'); }
        var agora = performance.now();
        vel = (e.clientX - ultimoX) / Math.max(agora - ultimoT, 1);
        ultimoX = e.clientX; ultimoT = agora;
        trilho.scrollLeft = inicioScroll - dx;
      });
      ouvir(window, 'pointerup', function () {
        if (!ativo) return;
        ativo = false;
        if (!moveu) return;
        var max = trilho.scrollWidth - trilho.clientWidth;
        gsap.to(trilho, {
          scrollLeft: Math.max(0, Math.min(max, trilho.scrollLeft - vel * 380)),
          duration: .8, ease: 'power3.out',
          onComplete: function () { trilho.classList.remove('arrastando'); }
        });
      });
      ouvir(trilho, 'click', function (e) { if (moveu) { e.preventDefault(); e.stopPropagation(); moveu = false; } }, true);
      ouvir(trilho, 'dragstart', function (e) { e.preventDefault(); });
    });

    return function () { limpar.forEach(function (f) { f(); }); };
  });

  /* ======================================================================
     6. Galeria do produto
     ====================================================================== */
  if (window.Flip) {
    BHD.animarGaleria = function (botao, principal, trocar) {
      var mini = $('img', botao);
      var r = mini.getBoundingClientRect();
      var voo = document.createElement('img');
      voo.src = botao.getAttribute('data-src');
      voo.alt = '';
      voo.className = 'galeria-voo';
      voo.style.cssText = 'left:' + r.left + 'px;top:' + r.top + 'px;width:' + r.width + 'px;height:' + r.height + 'px';
      document.body.appendChild(voo);
      window.Flip.fit(voo, principal, {
        duration: .7,
        ease: 'expo.inOut',
        absolute: true,
        onComplete: function () {
          trocar();
          gsap.to(voo, { opacity: 0, duration: .25, onComplete: function () { voo.remove(); } });
        }
      });
    };
  }
})();
