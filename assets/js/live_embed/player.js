const kit = require('../components/kanzakit');

class player {
  static startWatching(v) {
    video.addEventListener(
      'error',
      () => {
        player.showSplash('読み込み中に不明なエラーが発生しました...');
      },
      false
    );

    video.addEventListener(
      'ended',
      () => {
        clearInterval(config.heartbeat);
        player.showSplash('ストリームはオフラインです。');
      },
      false
    );

    video.addEventListener(
      'playing',
      () => {
        player.showSplash();
      },
      false
    );

    video.addEventListener(
      'canplay',
      () => {
        player.showSplash();
      },
      false
    );

    video.addEventListener(
      'loadedmetadata',
      () => {
        player.showSplash();
        setTimeout(() => {
          player.seekLive();
          window.config.play_suc_cnt = 0;
        }, 1000);
      },
      false
    );

    player.volume(80, true);
    if (localStorage.getItem('kplayer_mute'))
      player.mute(localStorage.getItem('kplayer_mute'));
    if (localStorage.getItem('kplayer_volume'))
      player.volume(localStorage.getItem('kplayer_volume'));
    if (config.myLive) player.mute(1, true);
  }

  static showStatus() {
    let buffer;
    try {
      buffer = video.buffered.end(0);
    } catch (e) {}
    const play = video.currentTime;
    let text = '';
    if (buffer > play && play && buffer && window.config.played !== play) {
      //再生
      text += `<a href="javascript:knzk.live_embed.player.seekLive()">LIVE</a>`;

      config.delay_sec = Math.round(buffer - play);
      text += ` · ${config.delay_sec}s`;

      player.showSplash();

      if (
        window.config.play_suc_cnt > 60 &&
        config.delay_sec > 5 &&
        !window.config.seek_sec
      ) {
        player.seekLive();
      }

      if (
        window.config.play_suc_cnt > 120 &&
        config.delay_sec > window.config.seek_sec &&
        window.config.seek_sec
      ) {
        window.config.seek_sec = 0;
        window.config.play_err_cnt = 0;

        console.log('lowlatency: enable');
      }

      window.config.play_suc_cnt++;
    } else {
      //バッファ
      text += 'BUFFERING';
      player.showSplash('バッファしています...');

      if (
        window.config.play_err_cnt > 5 &&
        !window.config.seek_sec &&
        window.config.play_suc_cnt
      ) {
        window.config.seek_sec = 5;
        window.config.play_suc_cnt = 0;

        console.log('lowlatency: disable');
        player.seekLive();
      }

      window.config.play_err_cnt++;
    }

    window.config.played = play;
    kit.elemId('video_status').innerHTML = text;
  }

  static showSplash(text = '') {
    kit.elemId('splash_loadtext').innerHTML = text;
    if (text) $('#splash').show();
    else $('#splash').hide();
  }

  static seekLive() {
    $('#play_button').hide();
    video.play().catch(e => {
      $('#play_button').show();
    });

    if (window.hls) {
      video.currentTime = hls.liveSyncPosition;
      return;
    }

    const delay = window.config.seek_sec ? window.config.seek_sec : 2;
    video.currentTime = video.buffered.end(0) - delay;
  }

  static mute(i = 0, no_save = false) {
    i = parseInt(i);
    kit.elemId('mute').className = i ? '' : 'invisible';
    kit.elemId('volume').className = !i ? '' : 'invisible';
    video.muted = i;
    if (!no_save) localStorage.setItem('kplayer_mute', i);
  }

  static volume(i, no_save = false) {
    kit.elemId('volume-range').value = i;
    video.volume = i * 0.01;
    if (!no_save) localStorage.setItem('kplayer_volume', i);
  }

  static full() {
    if (document.fullscreenElement) {
      document.exitFullscreen();
    } else {
      document.body.requestFullscreen();
    }
  }

  static end() {
    clearInterval(config.heartbeat);
    $('#video').hide();
    player.showSplash();
    $('#end_dialog').show();
  }

  static watchHover() {
    $('.hover').show();
    config.hover++;
    setTimeout(() => {
      config.hover--;

      if ($(':hover').length) {
        player.watchHover();
        return;
      }

      if (config.hover <= 0) {
        config.hover = 0;
        $('.hover').hide();
      }
    }, 5000);
  }
}

module.exports = player;
