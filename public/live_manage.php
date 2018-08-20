<?php
require_once("../lib/initload.php");

$my = getMe();
if (!$my) {
    http_response_code(403);
    exit("ERR:ログインしてください。");
}

if (!$my["liveNow"]) {
    header("Location: new");
    exit();
}
$live = getLive($my["liveNow"]);
if (!$live) {
    http_response_code(500);
    exit("ERR:問題が発生しました。管理者にお問い合わせください。");
}
$slot = getSlot($live["slot_id"]);

if ($_GET["mode"] && $_SESSION['csrf_token'] != $_GET['t']) {
  http_response_code(403);
  exit("ERROR: CSRF Challenge is failed");
}

if ($_GET["mode"] == "shutdown") {
    setSlot($live["slot_id"], 0);
    setUserLive(0);

    $mysqli = db_start();
    $stmt = $mysqli->prepare("UPDATE `live` SET is_live = 0 WHERE id = ?;");
    $stmt->bind_param("s", $live["id"]);
    $stmt->execute();
    $stmt->close();
    $mysqli->close();

    header("Location: ".$env["RootUrl"]);
    exit();
}
?>
<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO"
    crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
  <title>配信を管理 - <?=$env["Title"]?></title>
</head>
<body>
  <?php include "../include/navbar.php"; ?>
  <div class="container">
      <p>
          <b>配信URL:</b><br>
          <input class="form-control" type="text" value="<?=(empty($_SERVER["HTTPS"]) ? "http://" : "https://") . $_SERVER["HTTP_HOST"] . "/live?id=" . $live["id"] ?>" readonly>
      </p>
      <p>
          <b>配信サーバー情報:</b><br>
          <span class="text-danger">* このURLは漏洩すると第三者に配信を乗っ取られる可能性がありますので十分にご注意ください。</span><br>
      <div class="input-group">
        <div class="input-group-prepend">
          <span class="input-group-text" id="url">URL</span>
        </div>
        <input type="text" class="form-control" aria-describedby="url" readonly value="rtmp://<?=$slot["server"]?>/live?token=<?=$live["token"]?>">
      </div>
      <div class="input-group">
        <div class="input-group-prepend">
          <span class="input-group-text" id="key">ストリームキー</span>
        </div>
        <input type="text" class="form-control" aria-describedby="key" readonly value="<?=$live["id"]?>stream">
      </div>
      </p>
        <p>
          <b>配信を終了:</b><br>
          <span class="text-danger">* このボタンはソフト側(OBSなど)で配信終了してからクリックしてください。</span><br>
          <a href="live_manage?mode=shutdown&t=<?=$_SESSION['csrf_token']?>" onclick="return confirm('配信を終了して、配信枠を返却します。\nよろしいですか？');" class="btn btn-danger">配信を終了</a>
      </p>
  </div>

  <!-- Optional JavaScript -->
  <!-- jQuery first, then Popper.js, then Bootstrap JS -->
  <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo"
    crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49"
    crossorigin="anonymous"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy"
    crossorigin="anonymous"></script>
</body>
</html>