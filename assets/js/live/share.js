class share {
  static share() {
    if (navigator.share) {
      navigator.share({
        title: `${config.live.watch_data['name']} by ${
          config.live.account.name
        } - KnzkLive`,
        url: config.live.url
      });
    } else {
      $('#shareModal').modal('toggle');
    }
  }

  static share_modal(mode) {
    let url = '';
    const text = encodeURIComponent(
      `${config.live.watch_data['name']} by ${
        config.live.account.name
      } - #KnzkLive #${config.live.hashtag_o}`
    );
    if (mode === 'twitter') {
      url =
        `https://twitter.com/intent/tweet?url=${config.live.url}&text=` + text;
    } else if (mode === 'mastodon') {
      const instance = prompt(
        'Mastodonのドメインを入力してください',
        config.account.domain || config.domain
      );
      if (instance)
        url = `https://${instance}/share?text=${config.live.url} ${text}`;
    } else if (mode === 'facebook') {
      url = `https://www.facebook.com/sharer/sharer.php?u=${config.live.url}`;
    } else if (mode === 'line') {
      url = `http://line.me/R/msg/text/?${config.live.url}`;
    } else if (mode === 'weibo') {
      url = `http://service.weibo.com/share/share.php?url=${config.live.url}&title=${text}`;
    } else if (mode === 'skype') {
      url = `https://web.skype.com/share?url=${config.live.url}&text=${text}`;
    } else if (mode === 'flipboard') {
      url = `https://share.flipboard.com/bookmarklet/popout?v=2&url=${config.live.url}&title=${text}`;
    }
    window.open(url);
  }
}

module.exports = share;
