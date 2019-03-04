const kit = require('./components/kanzakit');
const toast = require('./components/toast');
const api = require('./components/api');

const livepage_donate = require('./live/donate');
const livepage_comment = require('./live/comment');

const comment_viewer = require('./comment_viewer');

class comment_loader {
  static load() {
    kit.elemId('err_comment').className = 'invisible';

    fetch(
      'https://' +
        config.main_domain +
        '/api/v1/timelines/tag/' +
        config.live.hashtag_o,
      {
        headers: { 'content-type': 'application/json' },
        method: 'GET'
      }
    )
      .then(function(response) {
        if (response.ok) {
          return response.json();
        } else {
          throw response;
        }
      })
      .then(function(json) {
        let reshtml = '';

        config.live.websocket.mastodon = new WebSocket(
          'wss://' +
            config.main_domain +
            '/api/v1/streaming/?stream=hashtag&tag=' +
            config.live.hashtag_o
        );
        config.live.websocket.mastodon.onopen = function() {
          config.live.heartbeat.mastodon = setInterval(
            () => config.live.websocket.mastodon.send('ping'),
            5000
          );
          config.live.websocket.mastodon.onmessage =
            config.live.page === 'livepage'
              ? livepage_comment.onmessage
              : comment_viewer.onmessage;

          config.live.websocket.mastodon.onclose = function() {
            kit.elemId('err_comment').className = '';
          };
        };

        config.live.websocket.knzk = new WebSocket(config.live.websocket_url);
        config.live.websocket.knzk.onopen = function() {
          config.live.heartbeat.knzk = setInterval(function() {
            if (
              config.live.websocket.knzk.readyState !== 0 &&
              config.live.websocket.knzk.readyState !== 1
            )
              config.live.websocket.knzk.close();
            config.live.websocket.knzk.send('ping');
          }, 5000);
        };
        config.live.websocket.knzk.onclose = function() {
          kit.elemId('err_comment').className = '';
        };

        config.live.websocket.knzk.onmessage = function(e) {
          const data = JSON.parse(e.data);
          if (data.type === 'pong' || !data.payload) return;
          if (data.event === 'delete') {
            kit.elemRemove(kit.elemId('post_' + data.payload));
            return;
          }

          const msg = JSON.parse(data.payload);
          if (config.live.page === 'livepage') {
            if (data.event === 'prop') {
              if (msg.type === 'vote_start') {
                kit.elemId('vote_title').textContent = msg.title;
                kit.elemId('vote1').textContent = msg.vote[0];
                kit.elemId('vote2').textContent = msg.vote[1];

                if (msg.vote[2]) {
                  kit.elemId('vote3').textContent = msg.vote[2];
                  kit.elemId('vote3').classList.remove('invisible');
                } else {
                  kit.elemId('vote3').classList.add('invisible');
                }

                if (msg.vote[3]) {
                  kit.elemId('vote4').textContent = msg.vote[3];
                  kit.elemId('vote4').classList.remove('invisible');
                } else {
                  kit.elemId('vote4').classList.add('invisible');
                }

                $('#prop_vote').show();
              } else if (msg.type === 'vote_end') {
                $('#prop_vote').hide();
                fetch(
                  config.endpoint +
                    'client/vote/reset' +
                    config.suffix +
                    '?id=' +
                    config.live.id,
                  {
                    method: 'GET',
                    credentials: 'include'
                  }
                );
              } else if (msg.type === 'item') {
                if (msg.item_type === 'knzk_kongyo') {
                  const volume = localStorage.getItem('kplayer_volume');
                  const mute = localStorage.getItem('kplayer_mute');
                  const audio = new Audio(
                    'https://static.knzk.me/knzklive/kongyo.mp3'
                  );
                  audio.volume = volume ? volume * 0.01 : 0.8;
                  audio.muted = parseInt(mute === null ? 0 : mute);
                  audio.play();
                  return;
                }
                if (msg.item_type === 'knzk_kongyo_kami') {
                  for (let i = 0; i < 50; i++) {
                    const audio = new Audio(
                      'https://static.knzk.me/knzklive/kongyo.mp3'
                    );
                    audio.volume = 1;
                    audio.muted = 0;
                    audio.play();
                  }
                  return;
                }
                kit
                  .elemId('iframe')
                  .contentWindow.knzk.live_embed.danmaku.run_item(
                    msg.item_type,
                    msg.item,
                    10
                  );
              } else if (msg.type === 'change_config') {
                if (msg.mode === 'sensitive' && msg.result) {
                  const frame = kit.elemId('iframe');
                  config.live.frame_url = frame.src;
                  frame.src = '';
                  $('#sensitiveModal').modal('show');
                } else if (msg.mode === 'comment') {
                  if (msg.result) {
                    $('.comment_block').show();
                  } else {
                    $('.comment_block').hide();
                  }
                } else if (msg.mode === 'ngs') {
                  comment_loader.getNgs();
                }
              } else if (msg.type === 'donate') {
                livepage_donate.add(msg);
              }
            } else if (data.event === 'update') {
              livepage_comment.onmessage(msg, 'update');
            }
          }

          if (config.live.page === 'comment_viewer') {
            if (data.event === 'prop') {
              if (msg.type === 'change_config') {
                if (
                  msg.mode === 'ngs' ||
                  (msg.mode === 'comment' && msg.result)
                )
                  location.reload();
                if (msg.mode === 'comment' && !msg.result)
                  kit.elemId('comments').style.display = 'none';
              } else if (msg.type === 'donate') {
                comment_viewer.addDonate(msg);
              }
            } else if (data.event === 'update') {
              comment_viewer.onmessage(msg, 'update');
            }
          }
        };

        api
          .request('client/comment_get', 'GET', { id: config.live.id })
          .then(c => {
            if (c) {
              json = json.concat(c);
              json.sort(function(a, b) {
                return Date.parse(a['created_at']) < Date.parse(b['created_at'])
                  ? 1
                  : -1;
              });
            }
            if (json) {
              let i = 0;
              const tmpl = Handlebars.compile(
                document.getElementById('com_tmpl').innerHTML
              );
              while (json[i]) {
                if (config.np.indexOf(json[i]['id']) === -1) {
                  reshtml += comment_loader.checkData(json[i])
                    ? tmpl(comment_loader.buildCommentData(json[i]))
                    : '';
                }
                i++;
              }
            }

            kit.elemId('comments').innerHTML = reshtml;
          })
          .catch(error => {
            console.error(error);
            kit.elemId('err_comment').className = '';
          });
      })
      .catch(error => {
        console.error(error);
        kit.elemId('err_comment').className = '';
      });
  }

  static closeAll(type, hide_toast = false) {
    if (type === 'mastodon' && config.live.websocket.knzk)
      config.live.websocket.knzk.close();
    else if (type === 'knzklive' && config.live.websocket.mastodon)
      config.live.websocket.mastodon.close();
    else {
      type = 'ロードエラー';
      if (config.live.websocket.knzk) config.live.websocket.knzk.close();
      if (config.live.websocket.mastodon)
        config.live.websocket.mastodon.close();
    }

    if (config.live.heartbeat.knzk) clearInterval(config.live.heartbeat.knzk);
    if (config.live.heartbeat.mastodon)
      clearInterval(config.live.heartbeat.mastodon);

    config.live.websocket.knzk = null;
    config.live.websocket.mastodon = null;

    config.live.heartbeat.mastodon = null;
    config.live.heartbeat.knzk = null;

    if (!hide_toast)
      toast.new('(' + type + ') コメントサーバーに再接続しています...');
    comment_loader.load();
  }

  static getNgs() {
    api
      .request('client/ngs/get', 'POST', { live_id: config.live.id })
      .then(json => {
        config.nw = json['w'] ? JSON.parse(atob(json['w'])) : [];
        config.nu = json['u'] ? JSON.parse(atob(json['u'])) : [];
        config.np = json['p'] ? JSON.parse(atob(json['p'])) : [];

        // 一度支援者リストをリセットする
        if (
          config.live.page === 'livepage' &&
          config.dn &&
          Object.keys(config.dn).length >= 0
        ) {
          for (let i in config.dn) {
            livepage_donate.delete(i);
          }
        }

        config.dn = {};
        if (json['donator']) {
          for (let item of json['donator']) {
            if (config.live.page === 'livepage') livepage_donate.add(item);
            else comment_viewer.addDonate(item);
          }
        }

        if (config.nu.indexOf('#ME#') !== -1 && config.live.page === 'livepage')
          location.reload();

        comment_loader.closeAll(null, true);
      });
  }

  static checkData(data) {
    let result = true;
    for (let item of config.nw) {
      if (
        data['content'].indexOf(item) !== -1 ||
        data['account']['display_name'].indexOf(item) !== -1
      ) {
        result = false;
        break;
      }
    }
    let acct =
      data['account']['acct'] !== data['account']['username']
        ? data['account']['acct'].replace(' (local)', '')
        : data['account']['username'] + '@' + config.main_domain;
    if (kit.search(config.nu, acct)) {
      result = false;
    }
    return result;
  }

  static checkDonator(acct) {
    let result = false;
    for (let item in config.dn) {
      if (config.dn[item] && config.dn[item]['account']['acct'] === acct) {
        const datet = parseInt(
          new Date(config.dn[item]['ended_at']).getTime() - new Date().getTime()
        );
        if (datet <= 0) {
          if (config.live.page === 'livepage')
            livepage_donate.delete(config.dn[item]['id']);
          else comment_viewer.deleteDonate(config.dn[item]['id']);
        } else {
          result = config.dn[item]['color'];
        }
        break;
      }
    }
    return result;
  }

  static msgreplace(str) {
    const reg = new RegExp(/(<br>|<br \/>)/, 'gm');
    return str.replace(reg, ' ');
  }

  static buildCommentData(data) {
    const acct =
      data['account']['acct'] !== data['account']['username']
        ? data['account']['acct'].replace(' (local)', '')
        : data['account']['username'] + '@' + config.main_domain;

    data['account']['display_name'] = kit.escape(
      data['account']['display_name']
    );

    data['donator_color'] = comment_loader.checkDonator(acct);

    data.content = comment_loader.msgreplace(data.content);

    return data;
  }
}

module.exports = comment_loader;
