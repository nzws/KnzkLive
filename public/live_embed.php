<?php
require_once("../lib/bootloader.php");
$_GET["id"] = s($_GET["id"]);
$live = getLive($_GET["id"]);
$liveUser = getUser($live["user_id"]);
$my = getMe();
if (!$_GET["id"] || !$live) {
  header("HTTP/1.1 404 Not Found");
  exit();
}
if (!$my && $live["privacy_mode"] == "3") {
  http_response_code(403);
  exit("ERR:この配信は非公開です。");
}
$myLive = $my["id"] == $live["user_id"];
if (!$myLive && $live["is_started"] == "0") {
  http_response_code(403);
  exit("ERR:この配信はまだ開始されていません。");
}
if (empty($_SESSION["watch_type"])) {
  $_SESSION["watch_type"] = preg_match('/(iPhone|iPad)/', $_SERVER['HTTP_USER_AGENT']) ? "HLS" : "FLV";
}
if (isset($_GET["watch_type"])) $_SESSION["watch_type"] = $_GET["watch_type"] == 0 ? "FLV" : "HLS";
$mode = $_SESSION["watch_type"];
?>
<!DOCTYPE html>
<html>
<head>
  <meta name="robots" content="noindex">
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.4.1/css/solid.css" integrity="sha384-osqezT+30O6N/vsMqwW8Ch6wKlMofqueuia2H7fePy42uC05rm1G+BUPSd2iBSJL" crossorigin="anonymous">
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.4.1/css/fontawesome.css" integrity="sha384-BzCy2fixOYd0HObpx3GMefNqdbA7Qjcc91RgYeDjrHTIEXqiF00jKvgQG0+zY/7I" crossorigin="anonymous">
  <link rel="stylesheet" href="<?=$env["RootUrl"]?>knzkitem.css?2019/02/04">
  <style>
    html,
    body {
      color: #f3f3f3;
      background: #212121;
      margin: 0;
      padding: 0;
      font-family: -apple-system, BlinkMacSystemFont, "Helvetica Neue", YuGothic, "ヒラギノ角ゴ ProN W3", Hiragino Kaku Gothic ProN, Arial, "メイリオ", Meiryo, sans-serif;
      font-size: 14px;
      user-select: none;
      overflow: hidden;
    }

    a,
    a:hover {
      color: #f3f3f3;
      text-decoration: none;
    }

    body {
      width: 100%;
      height: 100%;
    }

    video {
      width: 100%;
    }

    .invisible {
      display: none;
    }

    .footer {
      position: absolute;
      bottom: 0;
      width: 100%;
      z-index: 20000;
    }

    .header {
      position: absolute;
      top: 15px;
    }

    .live_user {
      left: 10px;
      z-index: 40000;
    }

    .live_user img {
      float: left;
      margin-right: 10px;
      width: 50px;
      height: 50px;
      border-radius: 5px;
      opacity: 0.8;
      background: #fff;
    }

    .watermark {
      opacity: .6;
      right: 20px;
      height: 18px;
    }

    .footer_content {
      padding: 10px;
    }

    .center_v {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      text-align: center;
    }

    .waiting_logo {
      width: 300px;
      max-width: 100%;
      margin-bottom: 10px;
    }

    .waiting_logo.animated {
      animation: pulse 3s infinite;
    }

    @keyframes pulse {
      0% {
        opacity: 1;
      }

      50% {
        opacity: .1;
      }

      100% {
        opacity: 1;
      }
    }

    .right {
      float: right
    }

    .video_control a {
      margin-left: 10px;
    }

    #volume-range {
      -webkit-appearance: none;
      background: #fafafa;
      height: 3px;
      width: 100px;
    }

    #volume-range::-webkit-slider-thumb {
      -webkit-appearance: none;
      background: #f3f3f3;
      height: 15px;
      width: 15px;
      border-radius: 100%;
    }

    #volume-range::-moz-range-track {
      height: 0;
    }

    #splash {
      position: absolute;
      bottom: 50px;
      left: 30px;
      font-size: 1.1rem;

      padding: 5px;
      background: rgba(0, 0, 0, .8);
      border-radius: 3px;
      z-index: 9000;
    }

    #play_button {
      position: absolute;
      width: 100%;
      bottom: 0;
      right: 0;
      left: 0;
      padding: 20px;
      padding-bottom: 50px;

      cursor: pointer;
      z-index: 10000;
      background: #ea356d;
      font-size: larger;
    }

    #play_button b {
      font-size: 1.2rem;
    }

    #play_button img {
      width: 280px;
      position: absolute;
      bottom: 0;
      right: 100px;
    }

    @media screen and (max-width: 768px) {
      #splash {
        left: 5px;
        bottom: 40px;
        font-size: 1rem;
      }

      #play_button img {
        display: none;
      }

      .volume_controller {
        display: none;
      }
    }
  </style>
</head>

<body>
<div id="play_button" style="display: none" onclick="seekLive()">
  <b>[クリックして再生]</b><br>
  <small>ブラウザが自動再生をブロックしました...</small>
  <img src="<?=$env["RootUrl"]?>images/surprized_knzk.png"/>
</div>

<div id="splash">
    <div id="splash_loadtext">配信サーバに接続しています...</div>
</div>

<div id="end_dialog" class="center_v" style="display: none">
    <img src="<?=$env["RootUrl"]?>images/knzklive_logo.png" class="waiting_logo animated"/>
    <p>
      配信は終了しました。
    </p>
</div>

<div id="video">
  <video id="knzklive" class="center_v" autoplay preload="auto">
    <p>
      KnzkLive Playerからのお知らせ:<br>
      この環境では視聴する事ができません。OS・ブラウザをアップデートするか、別の環境からお試しください。
    </p>
  </video>
  <img src="<?=$env["RootUrl"]?>images/knzklive_logo.png" class="watermark header"/>
  <div class="header live_user hover">
    <a href="<?=userUrl($liveUser["broadcaster_id"])?>" target="_blank" class="broadcaster_link">
      <img src="<?=$liveUser["misc"]["avatar"]?>"/>
      <b><?=$liveUser["name"]?></b>
    </a>
  </div>
  <div class="footer hover" style="background: rgba(0,0,0,.5)">
    <div class="footer_content">
      <span id="video_status">LOADING</span>
      <span> · <a href="?id=<?=$live["id"]?>&rtmp=<?=s($_GET["rtmp"])?>&watch_type=<?=($mode === "HLS" ? 0 : 1)?>"><?=s($mode)?></a></span>
      <span class="right video_control">
        <a href=""><i class="fas fa-sync-alt fa-fw"></i></a>

        <a href="javascript:mute()" id="mute" class="invisible"><i class="fas fa-volume-mute fa-fw"></i></a>
        <a href="javascript:mute(1)" id="volume"><i class="fas fa-volume-up fa-fw"></i></a>
        <span class="volume_controller">
          <span style="margin-right: 8px"></span>
          <input type="range" id="volume-range" onchange="volume(this.value)">
        </span>

        <a href="javascript:parent.widemode()"><i class="fas fa-arrows-alt-h fa-fw"></i></a>
        <a href="javascript:full()"><i class="fas fa-expand fa-fw"></i></a>
      </span>
    </div>
  </div>
</div>
<div id="item_layer"></div>
<div id="comment_layer"></div>

<script id="item_emoji_tmpl" type="text/x-handlebars-template">
  <div class="item_emoji {{class}}" style="{{style}}" id="{{random_id}}">
    {{repeat_helper}}
  </div>
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/handlebars.js/4.0.12/handlebars.min.js" integrity="sha256-qlku5J3WO/ehJpgXYoJWC2px3+bZquKChi4oIWrAKoI=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.slim.min.js" integrity="sha256-3edrmyuQ0w65f8gfBsqowzjJe2iM6n0nKciPUp8y+7E=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/flv.js/1.4.2/flv.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
<script>
  const type = '<?=s($mode)?>';
  const video = document.getElementById("knzklive");
  let myLive = <?=$myLive ? "true" : "false"?>;
  let delay_sec = 3;
  let heartbeat;
  let hover = 0;

  window.requestAnimationFrame = (function() {
  return window.requestAnimationFrame ||
    window.webkitRequestAnimationFrame ||
    window.mozRequestAnimationFrame ||
    window.msRequestAnimationFrame ||
    window.oRequestAnimationFrame ||
  function(f) { return window.setTimeout(f, 1000 / 120); };
}());
  function startWatching(v) {
    video.addEventListener("error", function() {
      showSplash("読み込み中に不明なエラーが発生しました...");
    }, false);

    video.addEventListener("ended", function() {
      showSplash("配信者からデータが送信されていません。");
    }, false);

    video.addEventListener("playing", function() {
      showSplash();
      v.play();
    }, false);

    video.addEventListener("canplay", function() {
      showSplash();
      v.play();
    }, false);

    video.addEventListener("loadedmetadata", function () {
      showSplash();
      v.play();
    }, false);

    volume(70, true);
    if (localStorage.getItem('kplayer_mute')) mute(localStorage.getItem('kplayer_mute'));
    if (localStorage.getItem('kplayer_volume')) volume(localStorage.getItem('kplayer_volume'));
    if (myLive) mute(1, true)
  }

  function showStatus() {
    let buffer;
    try {
      buffer = (video.buffered).end(0);
    } catch (e) {}
    const play = video.currentTime;
    let text = "";
    if (buffer > play && play && buffer) { //再生
      delay_sec = Math.round(buffer - play);
      if (type !== "HLS") {
        text += `<a href="javascript:seekLive()">LIVE</a> · ` + delay_sec + "s";
      } else {
        text += "LIVE";
      }
      showSplash();

      if (video.paused) {
        video.play().catch(function(e) {
          $("#play_button").show();
        });
      }
    } else { //バッファ
      text += "BUFFERING";
      showSplash("バッファしています...");
    }
    document.getElementById("video_status").innerHTML = text;
  }

  window.onload = function () {
    if (type !== "HLS" && flvjs.isSupported()) { //ws-flv
      const flvPlayer = flvjs.createPlayer({
        type: 'flv',
        isLive: true,
        url: '<?=(empty($_SERVER["HTTPS"]) ? "ws" : "wss")?>://<?=s($_GET["rtmp"])?>/live/<?=$live["id"]?>stream.flv'
      });
      flvPlayer.attachMediaElement(video);
      startWatching(flvPlayer);
      flvPlayer.load();
    } else { //hls
      const hls_url = `<?=(empty($_SERVER["HTTPS"]) ? "http" : "https")?>://<?=s($_GET["rtmp"])?>/live/<?=$live["id"]?>stream/index.m3u8`;
      if(Hls.isSupported()) {
        const hls = new Hls();
        hls.loadSource(hls_url);
        hls.attachMedia(video);
        hls.on(Hls.Events.MANIFEST_PARSED,function() {
          video.play();
        });
      } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
        video.src = hls_url;
        video.load();
      }
      startWatching(video);
    }
    heartbeat = setInterval(showStatus, 1000);

    setTimeout(function() {
      $(".hover").hide();
    }, 5000);
  };

  function showSplash(text = "") {
    document.getElementById("splash_loadtext").innerHTML = text;
    if (text) $("#splash").show();
    else $("#splash").hide();
  }

  function seekLive() {
    $("#play_button").hide();
    video.play();
    video.currentTime = (video.seekable).end(0) - 1;
  }

  function mute(i = 0, no_save) {
    i = parseInt(i);
    document.getElementById("mute").className = i ? "" : "invisible";
    document.getElementById("volume").className = !i ? "" : "invisible";
    video.muted = i;
    if (!no_save) localStorage.setItem('kplayer_mute', i);
  }

  function volume(i, no_save) {
    document.getElementById("volume-range").value = i;
    video.volume = i * 0.01;
    if (!no_save) localStorage.setItem('kplayer_volume', i);
  }

  function full() {
    const v = document.querySelector("body");
    let i;
    if (document.webkitCancelFullScreen) i = document.webkitFullscreenElement;
    if (document.mozCancelFullscreen) i = document.mozFullScreenElement;
    else if (document.exitFullscreen) i = document.fullscreenElement;

    if (i) {
      if (v.webkitRequestFullscreen) document.webkitCancelFullScreen(); //Webkit
      else if (v.mozRequestFullscreen) document.mozCancelFullscreen(); //Firefox
      else if (v.requestFullscreen) document.exitFullscreen();
    } else {
      if (v.webkitRequestFullscreen) v.webkitRequestFullscreen(); //Webkit
      if (v.mozRequestFullscreen) v.mozRequestFullscreen(); //Firefox
      else if (v.requestFullscreen) v.requestFullscreen();
    }
  }

  Handlebars.registerHelper('repeat_helper', function() {
    let html = "";
    for (let i = 0; i < this.repeat_num; i++) {
      html += this.repeat_html;
    }
    return new Handlebars.SafeString(html);
  });

  function run_item(type, value, clear_sec = 0) {
    value["random_id"] = "item_" + (Math.random().toString(36).slice(-8));

    const tmpl = Handlebars.compile(document.getElementById("item_" + type + "_tmpl").innerHTML);

    setTimeout(function () {
      $("#item_layer").append(tmpl(value));
      setTimeout(function () {
        const del = document.getElementById(value["random_id"]);
        if (del) del.parentNode.removeChild(del);
      }, clear_sec * 1000);
    }, delay_sec);
  }

  function comment_view(text) {
    const id = Math.floor(Math.random() * 1000000);
    const height = Math.floor( Math.random() * $("#comment_layer").height() - 40);
    const can = document.getElementById("#comment_layer");
    $("#comment_layer").prepend('<div id=' + id + '>' + text + '</div>');
    const width = $("#comment_layer").width()

    let i = 0
    function animation() {
      $('#' + id).css('right', i - text.length * 14) //1文字14px
      $('#' + id).css('bottom', height)
      i += 4
    }
    function scroll() {
      if(i < width + text.length * 14) {
        animation();
        requestAnimationFrame(scroll);
      } else {
        $('#' + id).remove()
      }
    }
    scroll();
  }
  function end() {
    clearInterval(heartbeat);
    $("#video").hide();
    showSplash();
    $("#end_dialog").show();
  }

  window.onmouseover = window.onclick = function watchHover() {
    $(".hover").show();
    hover++;
    setTimeout(function() {
      hover--;

      if ($(":hover").length) {
        watchHover();
        return;
      }

      if (hover <= 0) {
        hover = 0;
        $(".hover").hide();
      }
    }, 5000);
  }
</script>
</body>
</html>
