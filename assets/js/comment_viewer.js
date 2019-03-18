const kit = require('./components/kanzakit');

class comment_viewer {
  static ready() {
    knzk.comment_loader.getNgs();
  }

  static onmessage(message, mode = '') {
    let ws_resdata;
    let ws_reshtml;
    if (mode) {
      //KnzkLive Comment
      ws_resdata = {};
      ws_resdata.event = mode;
      ws_reshtml = message;
    } else {
      //Mastodon
      ws_resdata = JSON.parse(message.data);
      ws_reshtml = JSON.parse(ws_resdata.payload);
    }

    if (ws_resdata.event === 'update') {
      const tmpl = Handlebars.compile(kit.elemId('com_tmpl').innerHTML);
      if (ws_reshtml['id'] && knzk.comment_loader.checkData(ws_reshtml)) {
        $('#comments').prepend(
          tmpl(knzk.comment_loader.buildCommentData(ws_reshtml))
        );
      }
    } else if (ws_resdata.event === 'delete') {
      kit.elemRemove(kit.elemId(`post_${ws_resdata.payload}`));
    }
  }

  static addDonate(data) {
    config.dn[data['id']] = data;
    const datet = parseInt(
      new Date(data['ended_at']).getTime() - new Date().getTime()
    );
    setTimeout(() => {
      comment_viewer.deleteDonate(data['id']);
    }, datet);
  }

  static deleteDonate(id) {
    config.dn[id] = null;
  }
}

module.exports = comment_viewer;
