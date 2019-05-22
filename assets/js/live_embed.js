module.exports = {
  ready() {
    require('./components/prefixkit')();

    window.onmouseover = window.onclick = this.player.watchHover;

    Handlebars.registerHelper('repeat_helper', function() {
      let html = '';
      for (let i = 0; i < this.repeat_num; i++) {
        html += this.repeat_html;
      }
      return new Handlebars.SafeString(html);
    });

    if (config.type !== 'HLS' && flvjs.isSupported()) {
      // https://github.com/bilibili/flv.js/blob/master/docs/api.md
      const flvPlayer = flvjs.createPlayer(
        {
          type: 'flv',
          isLive: true,
          url: config.test_flv ? config.test_flv : config.flv
        },
        {
          enableStashBuffer: false,
          stashInitialSize: 1024 * 64, // 64KB
          enableWorker: true,
          autoCleanupSourceBuffer: true
        }
      );

      flvPlayer.attachMediaElement(video);
      this.player.startWatching(flvPlayer);
      flvPlayer.load();
    } else {
      //hls
      if (Hls.isSupported()) {
        const hls = new Hls();
        hls.loadSource(config.hls);
        hls.attachMedia(video);
        hls.on(Hls.Events.MANIFEST_PARSED, () => {
          video.play();
        });
      } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
        video.src = config.hls;
        video.load();
      }
      this.player.startWatching(video);
    }
    config.heartbeat = setInterval(this.player.showStatus, 1000);

    setTimeout(() => {
      $('.hover').hide();
    }, 5000);
  },
  player: require('./live_embed/player'),
  danmaku: require('./live_embed/danmaku')
};
