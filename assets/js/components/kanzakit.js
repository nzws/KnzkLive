/*
  Kanzaki(@knzkoniisan) + Kit + JavaScript = KanzaKit.js
*/

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

  static randInt(min = 0, max = 100) {
    return Math.floor(Math.random() * (max + 1 - min)) + min;
  }

  static elemRemove(element) {
    return element ? element.parentNode.removeChild(element) : false;
  }
}

module.exports = kanzakit;
