module.exports = {
  ready: function() {
    window.onmouseover = window.onclick = this.player.watchHover;
    window.requestAnimationFrame = (function() {
      return (
        window.requestAnimationFrame ||
        window.webkitRequestAnimationFrame ||
        window.mozRequestAnimationFrame ||
        window.msRequestAnimationFrame ||
        window.oRequestAnimationFrame ||
        function(f) {
          return window.setTimeout(f, 1000 / 120);
        }
      );
    })();

    Handlebars.registerHelper('repeat_helper', function() {
      let html = '';
      for (let i = 0; i < this.repeat_num; i++) {
        html += this.repeat_html;
      }
      return new Handlebars.SafeString(html);
    });

    if (config.type !== 'HLS' && flvjs.isSupported()) {
      //ws-flv
      const flvPlayer = flvjs.createPlayer({
        type: 'flv',
        isLive: true,
        url: config.flv
      });
      flvPlayer.attachMediaElement(video);
      this.player.startWatching(flvPlayer);
      flvPlayer.load();
    } else {
      //hls
      if (Hls.isSupported()) {
        const hls = new Hls();
        hls.loadSource(config.hls);
        hls.attachMedia(video);
        hls.on(Hls.Events.MANIFEST_PARSED, function() {
          video.play();
        });
      } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
        video.src = config.hls;
        video.load();
      }
      this.player.startWatching(video);
    }
    config.heartbeat = setInterval(this.player.showStatus, 1000);

    setTimeout(function() {
      $('.hover').hide();
    }, 5000);
  },
  player: require('./live_embed/player'),
  danmaku: require('./live_embed/danmaku')
};
