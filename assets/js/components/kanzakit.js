class kanzakit {
  static elemId(_id) {
    return document.getElementById(_id);
  }

  static escape(text) {
    try {
      const escape = text
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');

      return escape;
    } catch (e) {
      console.error(e);
      return false;
    }
  }
}

module.exports = kanzakit;
