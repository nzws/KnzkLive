/*
  Let's toast Knzk!
*/

const kit = require('../components/kanzakit');

class toast {
  static new(
    text,
    bgcolor = '.bg-primary',
    textcolor = '#fff',
    allow_html = false,
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
      bgcolor.includes('.') ? bgcolor.replace('.', '') : ''
    } knzk_toast`;
    element.style.background = !bgcolor.includes('.') ? bgcolor : '';
    element.style.color = textcolor;

    if (allow_html) {
      element.innerHTML = text;
    } else {
      element.textContent = text;
    }

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
