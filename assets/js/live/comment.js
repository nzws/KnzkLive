const kit = require('../components/kanzakit');
const api = require('../components/api');

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
          toastr.error(
            'サーバーと通信中にエラーが発生しました。<br>通信環境が正常かお確かめください。',
            'エラー'
          );
        });
    }
  }
}

module.exports = comment;
