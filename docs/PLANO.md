# BHD Móveis — Plano de execução

Resultado da etapa de descoberta (Grill Me), em 10/10/2026. Este documento separa o que já foi **decidido**, o que ainda está **pendente** e o que precisa ser **confirmado com a BHD**.

---

## 1. Decisões

### Produto e escopo
- Site vitrine, sem checkout. Compra pelo Mercado Livre, pela Amazon, pelo WhatsApp ou na loja física.
- Somente **móveis**. Planejados ficam fora até a BHD confirmar.
- Lançamento com **produtos fictícios** (até ~50) e fotos de banco de imagens (Unsplash), marcadas como ilustrativas. As fotos reais chegam no fim do mês.
- Cada produto define o **canal principal de compra** (padrão: Mercado Livre). Os outros canais aparecem como secundários e o WhatsApp está sempre disponível.

### Páginas
| Página | URL | Observação |
|---|---|---|
| Home | `/` | Seções na ordem abaixo |
| Categoria | `/sofas/`, `/mesas/`, `/cadeiras/`, `/diversos/` | Grade, ordenação por preço; filtros só se fizerem falta |
| Produto | `/sofas/sofa-lina-3-lugares/` | Galeria, preço, medidas, materiais, canais de compra, relacionados |
| Ofertas | `/ofertas/` | Também serve de landing page para anúncios |
| Unidades | `/unidades/` | 2 lojas: endereço, WhatsApp, horário, rota, avaliar |
| Sobre | `/sobre/` | |
| Privacidade | `/privacidade/` | Obrigatória por causa do Analytics e do Pixel |
| 404 | `/404.html` | |

Ficam de fora: página de contato (o WhatsApp e as Unidades cobrem), favoritos, carrinho e feed do Instagram embutido.

### Home (ordem das seções)
1. Faixa de benefícios: pronta-entrega, entrega em 24h, 18x. Discreta, sem cara de liquidação.
2. **Hero A:** título cinético, foto se abrindo como cortina e etiqueta de preço pendurada no produto.
3. Categorias. Sobe por cima do hero na transição de saída (sticky, sem travar o scroll).
4. **Ofertas da semana:** carrossel com rolagem lateral livre (arrastar ou setas), sem passar sozinho.
5. Compre do seu jeito: loja física, WhatsApp, Mercado Livre ou Amazon.
6. Mais vendidos / destaques.
7. Venha ver de perto: showroom e as 2 unidades, com Rota, WhatsApp e Avaliar.
8. Avalie sua unidade.
9. Perguntas frequentes: entrega, montagem, parcelamento, garantia e trocas.
10. Rodapé.

### Direção visual
- **Cores:** bordô `#6D012B` (medido no logo oficial), branco-papel `#FBFAF8`, linho `#EFE9E2`, grafite `#262226` e pedra `#6F6A66`.
- **Tipografia:** Bricolage Grotesque nos títulos e preços e Instrument Sans nos textos, ambas hospedadas no próprio site.
- **Elemento de marca:** a etiqueta de loja (hang tag) como suporte do preço.
- **Motion com GSAP** (gratuito, incluindo plugins; salvo no próprio site, carregado com `defer` só onde for usado):
  - Entrada do hero (Timeline + SplitText): título linha a linha, foto abrindo como cortina, etiqueta de preço que cai e balança no cordão.
  - Saída do hero (ScrollTrigger com scrub): categorias sobem por cima do hero, que recua e escurece. A rolagem continua nativa.
  - Carrossel de ofertas: Draggable + Inertia no desktop; no celular, rolagem nativa com encaixe.
  - Galeria do produto (Flip): a miniatura cresce até a posição da imagem principal.
  - Cards: leve balanço da etiqueta ao passar o mouse (quickTo), só em dispositivos com mouse.
  - **Não usar:** smooth scroll (Lenis/ScrollSmoother) nem revelação de cada seção ao rolar.
  - Conteúdo visível sem JavaScript; animações desligadas com `prefers-reduced-motion` (via `gsap.matchMedia()`).
- **Logo:** SVG fiel ao original (círculo bordô), SVG sem fundo em bordô e versão horizontal para header e rodapé.

### Avaliações do Google
- Não exibir nota nem quantidade por enquanto (hoje são 4,0 com 12 avaliações e 4,2 com 5).
- Chamada "Avalie sua unidade" com o link de cada loja e popup discreto. Aparece uma vez, pode ser fechado e não cobre CTAs no celular.
- Bloco com a nota real fica pronto e **desligado** em `data/site.json`.

### Tecnologia e hospedagem
- **Dados** em JSON e **templates** em PHP. O `php build.php`, rodado na máquina, gera HTML estático em `dist/`.
- O mesmo `dist/` funciona em qualquer hospedagem. **Preview:** Cloudflare Pages ou Netlify (grátis), com `noindex`. **Produção:** HostGator, no domínio `bhdmoveis.com.br`. A migração do WordPress fica com o dev.
- GitHub apenas como repositório remoto.
- Busca no header filtrando um `busca.json` gerado no build.

### Mensuração
- Analytics, Pixel e Clarity configurados em `data/site.json`, **desligados** até chegarem os IDs da BHD. Os IDs recebidos são da New Arrays e servem só de referência de estrutura.
- Os scripts só carregam após o aceite no banner de consentimento (LGPD).
- Eventos: `view_item`, `click_mercadolivre`, `click_amazon`, `click_whatsapp`, `click_rota`, `click_avaliacao`, `search`. Todos disparados por uma função única para GA4, Pixel e Clarity. Clique externo **não** é registrado como venda.

---

## 2. Arquitetura de pastas

```
BHD-moveis-site/
├─ data/
│  ├─ site.json          # contatos, IDs de analytics, flags (ex.: bloco de nota), caminho base
│  ├─ categorias.json
│  ├─ produtos.json
│  └─ unidades.json
├─ templates/
│  ├─ layout.php
│  ├─ partials/          # header, footer, card, etiqueta, whatsapp, popup, consentimento
│  └─ paginas/           # home, categoria, produto, ofertas, unidades, sobre, privacidade, 404
├─ assets/
│  ├─ css/               # tokens, base, componentes, páginas
│  ├─ js/                # main, motion, busca, analytics
│  ├─ fonts/
│  └─ img/               # logo, produtos (webp), unidades
├─ build.php             # JSON + templates → dist/
├─ dist/                 # gerado (ignorado no git)
├─ design/mockups/       # JPEGs de aprovação
└─ docs/                 # este plano + manual de manutenção
```

**Produto (exemplo de campos):** `id`, `slug`, `nome`, `categoria`, `descricao`, `preco`, `preco_anterior?`, `parcelas?`, `imagens[]`, `medidas?`, `materiais?`, `variacoes?`, `links { mercadolivre?, amazon? }`, `canal_principal`, `ativo`, `destaque`, `oferta`, `seo { titulo?, descricao? }`, `atualizado_em`. Os campos com `?` são opcionais e o template simplesmente não mostra o bloco quando faltam.

**SEO técnico:** title, description e canonical únicos por página, Open Graph, `sitemap.xml` e `robots.txt` gerados no build, breadcrumbs e dados estruturados `FurnitureStore` por unidade. O `Product` com `Offer` só entra quando os preços forem reais.

---

## 3. Etapas

| # | Etapa | Entrega |
|---|---|---|
| 1 | Logo | 3 SVGs + PNG em alta resolução |
| 2 | Base do site | Tokens, templates, JSON fictício (~24 produtos) e `build.php` funcionando |
| 3 | **Aprovação visual** | 7 JPEGs: Home, Categoria e Produto (desktop e celular) + Unidades (desktop) |
| 4 | Ajustes | Rodada de correções pedidas pelo Sodré e pelo cliente |
| 5 | Páginas e recursos restantes | Ofertas, Sobre, Privacidade, 404, busca, popup de avaliação, consentimento, analytics desligado |
| 6 | SEO técnico | Sitemap, robots, OG, schema, `noindex` no preview |
| 7 | QA | Playwright: links, console, responsividade (mobile, tablet, desktop, wide), acessibilidade, desempenho |
| 8 | Entrega | `docs/MANUTENCAO.md` (cadastrar produto, preço, categoria, unidade, publicar) + repositório pronto para versionar |

---

## 4. Confirmar com a BHD

- [ ] **Telefone e WhatsApp oficiais.** Hoje aparecem três números: (11) 93732-5839 no Google, (11) 99434-7741 no Instagram e no Facebook, e (11) 93322-2211 no site atual.
- [ ] **Unidade da R. Noronha Santos, 314 ("Outlet").** Aparece no site atual e no Instagram. Ainda existe? É uma terceira loja?
- [ ] **Horários** de cada unidade. O site atual diz seg–sáb 9h–19h30 e dom 10h–18h, e o Google diz outra coisa.
- [ ] **Pronta-entrega, entrega em até 24h em SP e parcelamento em até 18x** continuam valendo? Com ou sem juros? Entrega grátis?
- [ ] Vendem **planejados**? Querem isso no site?
- [ ] **Links de avaliação do Google** de cada unidade.
- [ ] **Produtos reais:** fotos, nomes, preços, medidas, materiais e links do Mercado Livre e da Amazon.
- [ ] Política de **trocas, garantia e montagem**, para a seção de Perguntas frequentes.
- [ ] **IDs próprios** de GA4, Meta Pixel e Clarity, se já existirem.
- [ ] Logo em arquivo vetorial original, se existir. Senão, usamos o SVG redesenhado.

## 5. Pendências internas

- [ ] Escolher o host de preview: Cloudflare Pages ou Netlify.
- [ ] Migração do domínio e e-mail (`contato@bhdmoveis.com.br`) do WordPress atual para a HostGator, com redirecionamentos 301. Fica com o dev, depois da aprovação.
