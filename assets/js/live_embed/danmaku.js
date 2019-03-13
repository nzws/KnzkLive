const kit = require('../components/kanzakit');

class danmaku {
  static run_item(type, value, clear_sec = 0) {
    value.random_id = `item_${kit.randInt(0, 10000)}`;

    const tmpl = Handlebars.compile(kit.elemId(`item_${type}_tmpl`).innerHTML);

    setTimeout(() => {
      if (value.type === 'random' && type === 'emoji') {
        for (let i = 0; i < value.count; i++) {
          setTimeout(() => {
            value.random_id = `item_${kit.randInt(0, 10000)}`;
            value.style = `top: ${kit.randInt(0, 99)}%;right: ${kit.randInt(
              0,
              99
            )}%;transform: rotate(${kit.randInt(-25, 25)}deg)`;
            $('#item_layer').append(tmpl(value));
            danmaku.delayRemove(value.random_id, 3);
          }, i * 50);
        }

        return;
      }

      $('#item_layer').append(tmpl(value));

      danmaku.delayRemove(value.random_id, clear_sec);
    }, config.delay_sec * 1000);
  }

  static delayRemove(id, sec) {
    return setTimeout(() => kit.elemRemove(kit.elemId(id)), sec * 1000);
  }

  static comment_view(text) {
    const id = kit.randInt(0, 10000);
    $('#comment_layer').prepend(`<div id=${id}>${text}</div>`);

    const height = $('#comment_layer').children().length * 35;
    const width = $('#comment_layer').width();

    let i = 0;
    function animation() {
      $(`#${id}`).css('right', i - text.length * 14); // 1文字14px
      $(`#${id}`).css('top', height);
      i += 2;
    }

    function scroll() {
      if (i < width + text.length * 14) {
        animation();
        requestAnimationFrame(scroll);
      } else {
        $(`#${id}`).remove();
      }
    }
    scroll();
  }
}

module.exports = danmaku;
