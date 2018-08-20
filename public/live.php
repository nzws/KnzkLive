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
if (!$my && $live["privacy_mode"] == "3") {
    http_response_code(403);
    exit("ERR:この配信は非公開です。");
}
$liveUser = getUser($live["user_id"]);
?>
<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO"
    crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
  <title><?=$live["name"]?> - <?=$env["Title"]?></title>
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
          <img src="<?=$liveUser["misc"]["avatar"]?>" class="avatar_img_navbar rounded-circle"/> <?=$liveUser["name"]?>
        </p>
        <p><?=$live["description"]?></p>
        <p class="invisible" id="err_live">
          * 配信を読み込めませんでした。まだデータが送信されていないか、配信に問題が発生している可能性があります。
        </p>
      </div>
      <div class="col-md-3">
        <?php if ($my) : ?>
          <div class="form-group">
            <textarea class="form-control" id="toot" rows="3" placeholder="コメント... (<?=$my["acct"]?>としてトゥート)" onkeyup="check_limit()"></textarea>
          </div>
          <div class="input-group">
            <button class="btn btn-primary" onclick="post_comment()">コメント</button>
            <b id="limit"></b>
          </div>
        <?php else : ?>
          <p>
            <span class="text-danger">* コメントを投稿するにはログインしてください。</span>
          </p>
        <?php endif; ?>

        <div id="comments"></div>
      </div>
    </div>
  </div>

<?php include "../include/footer.php"; ?>
  <script>
  const hashtag = " #knzklive_<?=$id?>";

    function startWatching() {
      check_limit();
    }

    function check_limit() {
      const l = document.getElementById("limit");
      if (!l) return; //未ログイン

      const d = document.getElementById("toot").value;
      const limit = 500 - hashtag.length - d.length;
      l.innerText = limit;
    }

    function checkLive() {
      fetch("http://<?=$slot["server"]?>/hls/<?=$id?>stream.m3u8", {
        method: 'GET'
      }).then(function (response) {
        if (response.ok) {
          return response.blob();
        } else {
          throw new Error();
        }
      }).then(function (data) {
        console.log("[OK]");
        startWatching();
      }).catch(function (error) {
        console.warn(error);
        document.getElementById("err_live").className = "text-danger";
      });
    }

    window.onload = function () {
      checkLive();
    };
  </script>
</body>
</html>