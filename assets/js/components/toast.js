/*
  Let's toast Knzk!
*/

const kit = require('../components/kanzakit');

class toast {
  static new(
    text,
    bgcolor = '.bg-primary',
    textcolor = '#fff',
    close_delay = 3500
  ) {
    if (!text) {
      console.error('[KnzkToast]', 'text is required!');
      return false;
    }

    const id = kit.randInt(0, 100000);
    const element = document.createElement('div');
    element.id = `toast_${id}`;
    element.className = `${
      bgcolor.indexOf('.') !== -1 ? bgcolor.replace('.', '') : ''
    } knzk_toast`;
    element.innerText = text;
    element.style.background = bgcolor.indexOf('.') === -1 ? bgcolor : '';
    element.style.color = textcolor;
    document.body.appendChild(element);

    if (close_delay > 0) {
      setTimeout(() => toast.close(id), close_delay);
    }

    return id;
  }

  static close(id) {
    const elem = kit.elemId(`toast_${id}`);

    if (elem) {
      elem.classList.add('hide');
      setTimeout(() => kit.elemRemove(elem), 200);
    }
  }
}

module.exports = toast;
