function elemId(_id) {
  return document.getElementById(_id)
}

function escapeHTML(text) {
  text = text
    .replace(/"/g, '"')
    .replace(/'/g, "'")
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')

  return text
}

function buildQuery(data) {
  var body = ''
  for (var key in data) {
    body += key + '=' + encodeURIComponent(data[key]) + '&'
  }
  body += 'd=' + new Date().getTime()
  return body
}
