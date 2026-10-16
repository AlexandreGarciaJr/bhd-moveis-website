// Converte as fotos de imagens-originais/ em WebP otimizado, em vários tamanhos.
//
//   imagens-originais/produtos/<slug>/01.jpg  →  assets/img/produtos/<slug>/01-480.webp, -960, -1440
//   imagens-originais/ambientes/<nome>.jpg    →  assets/img/ambientes/<nome>-960.webp, -1600, -2400
//
// Uso: npm run imagens            (só processa o que mudou)
//      npm run imagens -- --tudo  (refaz tudo)

import sharp from 'sharp';
import { readdirSync, statSync, mkdirSync, existsSync } from 'node:fs';
import { join, parse } from 'node:path';
import { fileURLToPath } from 'node:url';

const RAIZ = fileURLToPath(new URL('..', import.meta.url));
const ORIGEM = join(RAIZ, 'imagens-originais');
const DESTINO = join(RAIZ, 'assets', 'img');
const TUDO = process.argv.includes('--tudo');
const EXT = /\.(jpe?g|png|webp|avif|tiff?)$/i;

const GRUPOS = {
  produtos: { larguras: [480, 960, 1440], porPasta: true },
  ambientes: { larguras: [960, 1600, 2400], porPasta: false },
};

let feitos = 0;
let pulados = 0;

async function converter(arquivo, pastaSaida, larguras) {
  const { name } = parse(arquivo);
  const origemMs = statSync(arquivo).mtimeMs;
  mkdirSync(pastaSaida, { recursive: true });
  for (const largura of larguras) {
    const saida = join(pastaSaida, `${name}-${largura}.webp`);
    if (!TUDO && existsSync(saida) && statSync(saida).mtimeMs > origemMs) { pulados++; continue; }
    await sharp(arquivo)
      .rotate()
      .resize({ width: largura, withoutEnlargement: true })
      .webp({ quality: 74, effort: 5 })
      .toFile(saida);
    feitos++;
  }
}

for (const [grupo, { larguras, porPasta }] of Object.entries(GRUPOS)) {
  const base = join(ORIGEM, grupo);
  if (!existsSync(base)) continue;
  if (porPasta) {
    for (const slug of readdirSync(base)) {
      const pasta = join(base, slug);
      if (!statSync(pasta).isDirectory()) continue;
      for (const f of readdirSync(pasta).filter(f => EXT.test(f))) {
        await converter(join(pasta, f), join(DESTINO, grupo, slug), larguras);
      }
    }
  } else {
    for (const f of readdirSync(base).filter(f => EXT.test(f))) {
      await converter(join(base, f), join(DESTINO, grupo), larguras);
    }
  }
}

console.log(`Imagens geradas: ${feitos}. Já atualizadas: ${pulados}.`);
