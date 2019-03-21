const kit = require('../components/kanzakit');
const api = require('../components/api');
const toast = require('../components/toast');

const comment = require('./comment');

class live {
  static watch() {
    api
      .request('client/watch', 'GET', { id: config.live.id })
      .then(json => {
        const err = kit.elemId('err_live');
        err.innerHTML = '';

        if (json['live_status'] === 1)
          err.innerHTML = '配信者のプッシュを待っています...';
        if (
          json['live_status'] === 0 &&
          config.live.watch_data['live_status'] !== 0
        ) {
          err.innerHTML = 'この配信は終了しました。';
          live.widemode('hide');
          kit.elemId('count_open').className = 'invisible';
          kit.elemId('count_end').className = '';

          const frames = document.querySelectorAll('iframe');
          frames.forEach(frame => {
            frame.contentWindow.knzk.live_embed.player.end();
          });
        }
        if (
          json['live_status'] === 2 &&
          config.live.watch_data['live_status'] !== 2
        )
          live.reloadLive(true);

        kit.elemId('is_not_started').className = json['is_started']
          ? 'invisible'
          : 'text-warning';

        if (json['name'] !== config.live.watch_data['name']) {
          kit.elemId('live-name').innerText = json['name'];
          kit.elemId('title-name').innerText = `${json['name']} - KnzkLive`;
        }

        if (json['description'] !== config.live.watch_data['description'])
          kit.elemId('live-description').innerHTML = json['description'];

        if (json['viewers_count'] !== config.live.watch_data['viewers_count'])
          $('.count').html(json['viewers_count']);
        if (json['point_count'] !== config.live.watch_data['point_count'])
          $('.point_count').html(json['point_count']);
        if (json['viewers_max'] !== config.live.watch_data['viewers_max'])
          $('.max').html(json['viewers_max']);
        if (
          json['viewers_max_concurrent'] !==
          config.live.watch_data['viewers_max_concurrent']
        )
          kit.elemId('max_c').innerHTML = json['viewers_max_concurrent'];
        config.live.watch_data = json;
      })
      .catch(error => {
        console.error(error);
        kit.elemId('err_live').innerHTML =
          'データが読み込めません: ネットワークかサーバに問題が発生しています...';
      });
  }

  static date() {
    const now =
      config.live.watch_data['live_status'] === 0
        ? new Date(config.live.watch_data['ended_at'])
        : new Date();
    const datet = parseInt(
      (now.getTime() - new Date(config.live.created_at).getTime()) / 1000
    );

    let html = '';
    let hour = parseInt(datet / 3600);
    let min = parseInt((datet / 60) % 60);
    let sec = datet % 60;

    if (hour > 0) {
      if (hour < 10) hour = `0${hour}`;
      html += `${hour}:`;
    }

    if (min < 10) min = `0${min}`;
    html += `${min}:`;

    if (sec < 10) sec = `0${sec}`;
    html += sec;

    kit.elemId('time').innerHTML = html;
  }

  static update_watch() {
    return api.request('client/update_watching', 'GET', { id: config.live.id });
  }

  static reloadLive(only_main = false) {
    if (only_main) {
      const frame = kit.elemId('mainiframe');
      frame.src = frame.dataset.src;
    } else {
      const frames = document.querySelectorAll('iframe');
      frames.forEach(frame => {
        frame.src = frame.dataset.src;
      });
    }
  }

  static widemode(mode) {
    const is_wide =
      (document.body.className === 'is_wide' && !mode) || mode === 'hide';
    document.body.className = is_wide ? '' : 'is_wide';

    if (document.querySelectorAll('iframe').length > 1 && !is_wide)
      toast.new(
        '現在ワイドビューはメイン配信者にのみ対応しています。',
        '.bg-info',
        '#fff'
      );
  }

  static userDropdown(obj, id, acct, url) {
    let is_local = false;
    let local_icon = '';
    if (kit.search(acct, '(local)')) {
      // ローカル
      is_local = true;
      acct = acct.replace(' (local)', '');
      local_icon = `<i class="fas fa-home" title="ローカルコメント"></i> `;
    } else {
      // Mastodon
      if (!kit.search(acct, '@')) acct += `@${config.main_domain}`;
    }
    config.live.dropdown = {
      id: id,
      acct: acct
    };

    $('.user-dropdown').remove();
    let html = '';
    if (url)
      html += `<a class="dropdown-item" href="${url}" target="_blank">ウェブページに移動</a>`;

    if (config.live.is_broadcaster) {
      html += `
      <div class="dropdown-divider"></div>
      <a class="dropdown-item text-danger open_blocking_modal" href="#">ユーザーブロック</a>
      `;
      if (id)
        html += `<a class="dropdown-item text-danger comment_delete" href="#">投稿を削除</a>`;
    }

    $(obj).popover({
      title: '',
      content: 'aaaa',
      placement: 'bottom',
      trigger: 'focus',
      template: `
<div class="dropdown-menu user-dropdown">
  <h6 class="dropdown-header">${local_icon}@${acct}</h6>
  ${html}
  <div class="dropdown-divider"></div>
  <a class="dropdown-item text-muted" href="#" onclick="return false">閉じる</a>
</div>
`,
      html: true
    });
    $(obj).popover('show');

    $('.user-dropdown .open_blocking_modal').on('click', function() {
      open_blocking_modal(config.live.dropdown.acct);
    });

    $('.user-dropdown .comment_delete').on('click', function() {
      comment.delete(config.live.dropdown.id, config.live.dropdown.acct);
    });

    $('.user-dropdown').on('click', function() {
      $('.user-dropdown').popover('dispose');
    });
  }
}

module.exports = live;
