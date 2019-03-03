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
$myLive = $my["id"] === $live["user_id"];
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
  <link rel="stylesheet" href="<?=assetsUrl()?>bundle/live_embed.css?t=<?=filemtime(__DIR__ . "/bundle/live_embed.css")?>">
</head>

<body>
<div id="play_button" style="display: none" onclick="knzk.live_embed.player.seekLive()">
  <b>[クリックして再生]</b><br>
  <small>(ブラウザが自動再生をブロックしました...)</small>
  <img src="<?=assetsUrl()?>static/surprized_knzk.png"/>
</div>

<div id="splash">
    <div id="splash_loadtext">接続しています...</div>
</div>

<div id="end_dialog" class="center_v" style="display: none">
    <img src="<?=assetsUrl()?>static/knzklive_logo.png" class="waiting_logo animated"/>
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
  <img src="<?=assetsUrl()?>static/knzklive_logo.png" class="watermark header"/>
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

        <a href="javascript:knzk.live_embed.player.mute()" id="mute" class="invisible"><i class="fas fa-volume-mute fa-fw"></i></a>
        <a href="javascript:knzk.live_embed.player.mute(1)" id="volume"><i class="fas fa-volume-up fa-fw"></i></a>
        <span class="volume_controller">
          <span style="margin-right: 8px"></span>
          <input type="range" id="volume-range" onchange="knzk.live_embed.player.volume(this.value)">
        </span>

        <a href="javascript:parent.live.live.widemode()"><i class="fas fa-arrows-alt-h fa-fw"></i></a>
        <a href="javascript:knzk.live_embed.player.full()"><i class="fas fa-expand fa-fw"></i></a>
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
<script src="<?=assetsUrl()?>bundle/bundle.js?t=<?=filemtime(__DIR__ . "/../public/bundle/bundle.js")?>"></script>
<script>
  window.video = document.getElementById("knzklive");

  window.config = {
    type: '<?=s($mode)?>',
    myLive: <?=$myLive ? "true" : "false"?>,
    flv: '<?=(empty($_SERVER["HTTPS"]) ? "ws" : "wss")?>://<?=s($_GET["rtmp"])?>/live/<?=$live["id"]?>stream.flv',
    hls: '<?=(empty($_SERVER["HTTPS"]) ? "http" : "https")?>://<?=s($_GET["rtmp"])?>/live/<?=$live["id"]?>stream/index.m3u8',
    heartbeat: null,
    delay_sec: 3,
    hover: 0
  };

  window.onload = function() {
    knzk.live_embed.ready();
  }
</script>
</body>
</html>
