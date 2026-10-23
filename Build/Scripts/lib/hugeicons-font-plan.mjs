export const FIRST_CODEPOINT = 0xe001;
export const LAST_CODEPOINT = 0xf8ff;
export const FONT_CAPACITY = LAST_CODEPOINT - FIRST_CODEPOINT + 1;

/** Preserve published (font, codepoint) pairs; append new names into BMP shards. */
export function planIconFonts(slugs, previous) {
  if (previous?.version !== 1 || !Array.isArray(previous.fonts)) {
    throw new Error('Unsupported HugeIcons codepoint manifest');
  }
  const seen = new Set();
  const validSlug = slug => typeof slug === 'string' && /^[a-z0-9]+(?:-[a-z0-9]+)*$/.test(slug);
  const fonts = previous.fonts.map((font, index) => {
    const expectedName = index === 0 ? 'hugeicons' : `hugeicons-${index + 1}`;
    if (font.name !== expectedName || !Array.isArray(font.icons) || font.icons.length > FONT_CAPACITY) {
      throw new Error(`Invalid HugeIcons font shard ${expectedName}`);
    }
    for (const slug of font.icons) {
      if (!validSlug(slug) || seen.has(slug)) {
        throw new Error(`Invalid or duplicate HugeIcons mapping: ${slug}`);
      }
      seen.add(slug);
    }
    return { name: font.name, icons: [...font.icons] };
  });
  for (const slug of [...new Set(slugs)].sort()) {
    if (!validSlug(slug)) {
      throw new Error(`Invalid HugeIcons name: ${slug}`);
    }
    if (seen.has(slug)) continue;
    let font = fonts.at(-1);
    if (!font || font.icons.length === FONT_CAPACITY) {
      font = { name: fonts.length === 0 ? 'hugeicons' : `hugeicons-${fonts.length + 1}`, icons: [] };
      fonts.push(font);
    }
    font.icons.push(slug);
    seen.add(slug);
  }
  return { version: 1, fonts };
}
