const kit = require('../components/kanzakit');
const api = require('../components/api');

const toast = require('../components/toast');

// const common = require('../comment_loader');
// ここから読み取れなくて謎、とりあえず knzk.comment_loader 使えば取れる

class comment {
  static check_limit() {
    const toot = kit.elemId('toot');

    if (!config.account) {
      toot.value =
        'コメントするにはログインするか、 #' +
        config.live.hashtag_o +
        ' でトゥートしてください。';
      toot.disabled = true;
      return;
    }
    const d = toot.value;

    const result =
      (config.account.domain === 'twitter.com' ? 140 : 500) -
      config.live.hashtag.length -
      d.length;
    toot.maxLength = parseInt(result);
  }

  static toggleLocal() {
    const s = kit.elemId('no_toot');
    const bt = kit.elemId('comment_local_button');
    bt.classList.remove('btn-outline-primary', 'btn-primary');
    if (s.value) {
      s.value = '';
      bt.classList.add('btn-outline-primary');
    } else {
      s.value = '1';
      bt.classList.add('btn-primary');
    }
  }

  static post() {
    const box = kit.elemId('toot');
    const v = box.value;
    box.value = '';

    const space = v.replace(/ /gi, '').replace(/　/gi, '');
    if (!space) return;

    const isKnzk = !!kit.elemId('no_toot').value;

    if (isKnzk || config.account.domain === 'twitter.com') {
      api
        .request('client/comment_post', 'POST', {
          live_id: config.live.id,
          content: v,
          is_local: isKnzk ? 1 : 0,
          content_tw: v + config.live.hashtag
        })
        .catch(error => {
          box.value = v;
        });
    } else {
      fetch(`https://${config.account.domain}/api/v1/statuses`, {
        headers: {
          'content-type': 'application/json',
          Authorization: `Bearer ${config.account.token}`
        },
        method: 'POST',
        body: JSON.stringify({
          status: v + config.live.hashtag,
          visibility: 'public'
        })
      })
        .then(response => {
          if (response.ok) {
            return response.json();
          } else {
            throw response;
          }
        })
        .then(json => {
          if (json) {
            kit.elemId('toot').value = '';
          }
        })
        .catch(error => {
          console.log(error);
          toast.new(
            'サーバーと通信中にエラーが発生しました。通信環境が正常かお確かめください。',
            '.bg-danger'
          );
          box.value = v;
        });
    }
  }

  static delete(id, acct) {
    if (!config.live.is_broadcaster && !config.live.is_collabo) return false;

    if (
      confirm(
        `${acct}の投稿を削除します。よろしいですか？\n* SNSに同時投稿している場合はKnzkLiveでのみ非表示になります。`
      )
    ) {
      api
        .request('client/live/comment_delete', 'POST', {
          delete_id: id.replace('knzklive_', ''),
          live_id: config.live.id,
          is_knzklive: id.includes('knzklive_') ? 1 : 0
        })
        .then(json => {
          if (!json['success']) {
            toast.warn(
              'エラーが発生しました。データベースに問題が発生している可能性があります。',
              '.bg-danger'
            );
          }
        });
    }
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
      if (ws_reshtml['id']) {
        kit.elemId('comment_count').textContent =
          parseInt(kit.elemId('comment_count').textContent) + 1;
        const tmpl = Handlebars.compile(
          document.getElementById('com_tmpl').innerHTML
        );

        if (knzk.comment_loader.checkData(ws_reshtml)) {
          $('#comments').prepend(
            tmpl(knzk.comment_loader.buildCommentData(ws_reshtml))
          );
          kit
            .elemId('mainiframe')
            .contentWindow.knzk.live_embed.danmaku.comment_view(
              ws_reshtml['content']
            );
        }
      }
    } else if (ws_resdata.event === 'delete') {
      kit.elemRemove(kit.elemId(`post_${ws_resdata.payload}`));
    }
  }
}

module.exports = comment;
