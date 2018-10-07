function elemId(_id) {
  return document.getElementById(_id);
}

function escapeHTML(text) {
  text = text
  .replace(/"/g, '"')
  .replace(/'/g, "'")
  .replace(/&/g, '&amp;')
  .replace(/</g, '&lt;')
  .replace(/>/g, '&gt;');

  return text;
}