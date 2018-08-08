<?php
require_once("../lib/initload.php");

$id = s($_GET["id"]);
if (!$id) {
  http_response_code(421);
  exit("ERR:配信IDを入力してください。");
}

$live = getLive($id);
if (!$live) {
    http_response_code(404);
    exit("ERR:この配信は存在しません。");
}

if ($live["is_live"] == 0) {
    http_response_code(404);
    exit("ERR:この配信は終了しています。");
}
$slot = getSlot($live["slot_id"]);
$my = getMe();
?>
<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO"
    crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
  <title><?=$live["name"]?> - KnzkLive</title>
</head>
<body>
  <?php $navmode = "fluid"; include "../include/navbar.php"; ?>
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-9">
        <div class="embed-responsive embed-responsive-16by9" id="live">
          <iframe class="embed-responsive-item" src="live_embed?id=<?=$id?>&rtmp=<?=$slot["server"]?>" allowfullscreen></iframe>
        </div>
        <p>
          <h3><?=$live["name"]?></h3>
        </p> 
        <p class="invisible" id="err_live">
          * 配信を読み込めませんでした。まだデータが送信されていないか、配信に問題が発生している可能性があります。
        </p>
      </div>
      <div class="col-md-3">
        <div class="form-group">
          <textarea class="form-control" id="toot" rows="3" placeholder="コメント... (<?=$my["acct"]?>としてトゥート)"></textarea>
        </div>
        <button class="btn btn-primary" >コメント</button>
      </div>
    </div>
  </div>

  <!-- Optional JavaScript -->
  <!-- jQuery first, then Popper.js, then Bootstrap JS -->
  <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo"
    crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49"
    crossorigin="anonymous"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy"
    crossorigin="anonymous"></script>
  <script>
    function checkLive() {
      fetch("http://<?=$slot["server"]?>/hls/<?=$id?>.m3u8", {
        method: 'GET'
      }).then(function (response) {
        if (response.ok) {
          return response.blob();
        } else {
          throw new Error();
        }
      }).then(function (data) {
        console.log("[OK]");
        //startWatching();
      }).catch(function (error) {
        console.warn(error);
        document.getElementById("err_live").className = "text-danger";
      });
    }
    window.onload = function () {
      checkLive();
    };

    function startWatching() {

    }
  </script>
</body>
</html>