<?php
require_once("../lib/initload.php");
$_GET["id"] = s($_GET["id"]);
if (!$_GET["id"]) {
    header("HTTP/1.1 404 Not Found");
    exit();
}
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
    <video id="my-video" class="video-js" controls preload="auto" data-setup="{}">
    <source src="http://<?=$_GET["rtmp"]?>/hls/<?=$_GET["id"]?>.m3u8" type='application/x-mpegURL'>
    <p class="vjs-no-js">
      To view this video please enable JavaScript, and consider upgrading to a web browser that
      <a href="https://videojs.com/html5-video-support/" target="_blank">supports HTML5 video</a>
    </p>
  </video>

    <script src="https://vjs.zencdn.net/7.1.0/video.js"></script>
</body>
</html>