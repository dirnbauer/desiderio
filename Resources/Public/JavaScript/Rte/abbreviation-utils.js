/**
 * Helper for the Desiderio abbreviation plugin: concatenates the text
 * content of a model range (CKEditor 5 abbreviation tutorial helper).
 */
export function getRangeText(range) {
  let result = '';
  for (const item of range.getItems()) {
    if (item.is('$textProxy') || item.is('$text')) {
      result += item.data;
    }
  }
  return result;
}
