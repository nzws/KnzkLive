const kit = require('../components/kanzakit');
const api = require('../components/api');

// const common = require('../comment_loader');
// ここから読み取れなくて謎、とりあえず knzk.comment_loader 使えば取れる

class comment {
  static check_limit() {
    if (!config.account) return; //未ログイン
    const d = kit.elemId('toot').value;

    const result =
      (config.account.domain === 'twitter.com' ? 140 : 500) -
      config.live.hashtag.length -
      d.length;
    kit.elemId('limit').innerText = result;
  }

  static post() {
    const v = kit.elemId('toot').value;
    if (!v) return;
    const isKnzk = kit.elemId('no_toot').checked;

    if (isKnzk || config.account.domain === 'twitter.com') {
      api
        .request('client/comment_post', 'POST', {
          live_id: config.live.id,
          content: v,
          is_local: isKnzk ? 1 : 0,
          content_tw: v + config.live.hashtag
        })
        .then(function(json) {
          if (json) {
            kit.elemId('toot').value = '';
            comment.check_limit();
          }
        });
    } else {
      fetch('https://' + config.account.domain + '/api/v1/statuses', {
        headers: {
          'content-type': 'application/json',
          Authorization: 'Bearer ' + config.account.token
        },
        method: 'POST',
        body: JSON.stringify({
          status: v + config.live.hashtag,
          visibility: 'public'
        })
      })
        .then(function(response) {
          if (response.ok) {
            return response.json();
          } else {
            throw response;
          }
        })
        .then(function(json) {
          if (json) {
            kit.elemId('toot').value = '';
            comment.check_limit();
          }
        })
        .catch(error => {
          console.log(error);
          toast.new(
            'サーバーと通信中にエラーが発生しました。通信環境が正常かお確かめください。',
            '.bg-danger'
          );
        });
    }
  }

  static delete(id, acct) {
    if (!config.live.is_broadcaster) return false;

    if (
      confirm(
        acct +
          'の投稿を削除します。よろしいですか？\n* SNSに同時投稿している場合はKnzkLiveでのみ非表示になります。'
      )
    ) {
      api
        .request('client/live/comment_delete', 'POST', {
          delete_id: id.replace('knzklive_', ''),
          live_id: config.live.id,
          is_knzklive: id.indexOf('knzklive_') !== -1 ? 1 : 0
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
    let ws_resdata, ws_reshtml;
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
          kit.elemId('comments').innerHTML =
            tmpl(knzk.comment_loader.buildCommentData(ws_reshtml)) +
            kit.elemId('comments').innerHTML;
          kit
            .elemId('iframe')
            .contentWindow.knzk.live_embed.danmaku.comment_view(
              ws_reshtml['content']
            );
        }
      }
    } else if (ws_resdata.event === 'delete') {
      kit.elemRemove(kit.elemId('post_' + ws_resdata.payload));
    }
  }
}

module.exports = comment;
