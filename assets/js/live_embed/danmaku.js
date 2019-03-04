const kit = require('../components/kanzakit');

class danmaku {
  static run_item(type, value, clear_sec = 0) {
    value['random_id'] = 'item_' + kit.randInt(0, 10000);

    const tmpl = Handlebars.compile(
      kit.elemId('item_' + type + '_tmpl').innerHTML
    );

    setTimeout(() => {
      $('#item_layer').append(tmpl(value));
      setTimeout(
        () => kit.elemRemove(kit.elemId(value['random_id'])),
        clear_sec * 1000
      );
    }, config.delay_sec * 1000);
  }

  static comment_view(text) {
    const id = kit.randInt(0, 10000);
    $('#comment_layer').prepend('<div id=' + id + '>' + text + '</div>');

    const height = Math.floor(
      Math.random() * $('#comment_layer').height() - 40
    );
    const width = $('#comment_layer').width();

    let i = 0;
    function animation() {
      $('#' + id).css('right', i - text.length * 14); // 1文字14px
      $('#' + id).css('bottom', height);
      i += 4;
    }

    function scroll() {
      if (i < width + text.length * 14) {
        animation();
        requestAnimationFrame(scroll);
      } else {
        $('#' + id).remove();
      }
    }
    scroll();
  }
}

module.exports = danmaku;
