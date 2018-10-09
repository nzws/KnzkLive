<?php
require_once("../lib/bootloader.php");
$_GET["id"] = s($_GET["id"]);
$live = getLive($_GET["id"]);
if (!$_GET["id"] || !$live) {
    header("HTTP/1.1 404 Not Found");
    exit();
}
if (!getMe() && $live["privacy_mode"] == "3") {
    http_response_code(403);
    exit("ERR:この配信は非公開です。");
}
$mode = $_SESSION["watch_mode"];
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="robots" content="noindex">
    <link href="https://vjs.zencdn.net/7.1.0/video-js.css" rel="stylesheet">
    <style>
        html,
        body,
        .video-js {
            width: 100%;
            height: 100%;
            margin: 0;
            padding: 0;
        }
    </style>
</head>

<body>
    <video id="my-video" class="video-js" controls preload="auto" data-setup="{}" autoplay>
    <?php if ($mode === "hls") : ?><source src="http://<?=$_GET["rtmp"]?>/live/<?=$_GET["id"]?>stream/index.m3u8" type='application/x-mpegURL'><?php endif; ?>
    <?php if ($mode === "rtmp") : ?><source src="rtmp://<?=$_GET["rtmp"]?>/<?=$_GET["id"]?>stream" type='rtmp/mp4'><?php endif; ?>
    <?php if ($mode === "http-flv") : ?><source src="<?=(empty($_SERVER["HTTPS"]) ? "http" : "https")?>://<?=$_GET["rtmp"]?>/live/<?=$_GET["id"]?>stream.flv" type='video/x-flv'><?php endif; ?>
    <p class="vjs-no-js">
      To view this video please enable JavaScript, and consider upgrading to a web browser that
      <a href="https://videojs.com/html5-video-support/" target="_blank">supports HTML5 video</a>
    </p>
  </video>

    <script src="https://vjs.zencdn.net/7.1.0/video.js"></script>
<?php if ($mode === "dash") : ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dashjs/2.9.0/dash.all.min.js" integrity="sha256-WQRlnkRVJncPG+GSENXHuEb84m29r6Tm81aPxTmtZZ8=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/videojs-contrib-dash/2.10.0/videojs-dash.min.js" integrity="sha256-xhLRr5mlvCCC7DndQjNURZOXGxwYUoB2VoF0mNUiuJc=" crossorigin="anonymous"></script>
<script>
var player = videojs('my-video');

player.ready(function() {
  player.src({
    src: '<?=(empty($_SERVER["HTTPS"]) ? "http" : "https")?>://<?=$_GET["rtmp"]?>/live/<?=$_GET["id"]?>stream/index.mpd',
    type: 'application/dash+xml'
  });

  player.play();
});
</script>
<?php elseif ($mode === "http-flv") : ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/flv.js/1.4.2/flv.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/videojs-flvjs@0.2.0/dist/videojs-flvjs.min.js" integrity="sha256-9E4vlrJpHeWFm/dzSKOps4Csfx0X1ReuUX43FWEeSJE=" crossorigin="anonymous"></script>
    <script>
        const player = videojs('my-video', {
            techOrder: ['html5', 'flvjs'],
            flvjs: {
                mediaDataSource: {
                    isLive: true,
                    cors: true,
                    withCredentials: false,
                },
                // config: {},
            },
        });

        player.ready(function() {
            player.play();
        })
    </script>
<?php endif; ?>
</body>
</html>