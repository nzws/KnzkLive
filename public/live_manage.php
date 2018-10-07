<?php
require_once("../lib/bootloader.php");

$my = getMe();
if (!isset($my)) {
  http_response_code(403);
  exit("ERR:ログインしてください。");
}

if (!$my["liveNow"]) {
  header("Location: ".u("new"));
  exit();
}
$live = getLive($my["liveNow"]);
if (!isset($live)) {
  http_response_code(500);
  exit("ERR:問題が発生しました。管理者にお問い合わせください。");
}
$slot = getSlot($live["slot_id"]);

if (isset($_GET["mode"])) {
  if ($_SESSION['csrf_token'] != $_GET['t']) {
    http_response_code(403);
    exit("ERROR: CSRF Challenge is failed");
  }

  if ($_GET["mode"] == "shutdown") {
    setSlot($live["slot_id"], 0);
    setUserLive(0);

    if (setLiveStatus($live["id"], 0)) header("Location: ".u());
    else echo "ERROR: setLiveStatus";

    exit();
  }
}

if (isset($_POST["title"]) && isset($_POST["description"])) {
  $mysqli = db_start();
  $stmt = $mysqli->prepare("UPDATE `live` SET name = ?, description = ? WHERE id = ?;");
  $stmt->bind_param('sss', s($_POST["title"]), s($_POST["description"]), $live["id"]);
  $stmt->execute();
  $stmt->close();
  $mysqli->close();
  $live = getLive($live["id"]);
}

$liveurl = (empty($_SERVER["HTTPS"]) ? "http://" : "https://") . $_SERVER["HTTP_HOST"] . u("live") . "?id=" . $live["id"];
$comurl = (empty($_SERVER["HTTPS"]) ? "http://" : "https://") . $_SERVER["HTTP_HOST"] . u("api/client/comment_viewer") . "?id=" . $live["id"];
?>
<!doctype html>
<html lang="ja">
<head>
  <?php include "../include/header.php"; ?>
  <title>配信を管理 - <?=$env["Title"]?></title>
</head>
<body>
<?php include "../include/navbar.php"; ?>

<div class="container">
  <form method="post">
    <input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']?>">
    <div class="form-group">
      <label for="title">配信タイトル</label>
      <input type="text" class="form-control" id="title" name="title" aria-describedby="title_note" placeholder="タイトル" required value="<?=$live["name"]?>">
      <small id="title_note" class="form-text text-muted">100文字以下</small>
    </div>

    <div class="form-group">
      <label for="description">配信の説明</label>
      <textarea class="form-control" id="description" name="description" rows="4" required><?=$live["description"]?></textarea>
    </div>

    <button type="submit" class="btn btn-primary">更新</button>
  </form>
</div>

<hr>

<div class="container">
  <div class="box">
    <div class="row">
      <div class="col-md-6">
        <b>配信URL:</b><br>
        <div class="input-group">
          <input class="form-control" type="text" value="<?=$liveurl?>" readonly>
          <div class="input-group-append">
            <a class="btn btn-primary" href="<?=$liveurl?>" target="_blank">Open</a>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <b>コメビュURL:</b><br>
        <div class="input-group">
          <input class="form-control" type="text" value="<?=$comurl?>" readonly>
          <div class="input-group-append">
            <a class="btn btn-secondary" href="<?=$comurl?>" target="_blank">Open</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="box">
    <b>配信サーバー情報:</b><br>
    <span class="text-danger">* このURLは漏洩すると第三者に配信を乗っ取られる可能性がありますので十分にご注意ください。</span><br>
    <div class="row">
      <div class="col-md-6">
        <div class="input-group">
          <div class="input-group-prepend">
            <span class="input-group-text" id="url">URL</span>
          </div>
          <input type="text" class="form-control" aria-describedby="url" readonly value="rtmp://<?=$slot["server_ip"]?>/live">
        </div>
      </div>
      <div class="col-md-6">
        <div class="input-group">
          <div class="input-group-prepend">
            <span class="input-group-text" id="key">ストリームキー</span>
          </div>
          <input type="text" class="form-control" aria-describedby="key" readonly placeholder="クリックで表示" onclick="window.prompt('ストリームキー', '<?=$live["id"]?>stream?token=<?=$live["token"]?>')">
        </div>
      </div>
    </div>
  </div>

  <div class="box">
    <b>配信を終了:</b><br>
    <?php if ($live["is_live"] === 2) : ?>
      <span class="text-danger">* ソフト側(OBSなど)で配信終了するとボタンが使用できます。</span><br>
    <?php elseif ($live["is_live"] === 1) : ?>
      <a href="<?=u("live_manage")?>?mode=shutdown&t=<?=$_SESSION['csrf_token']?>" onclick="return confirm('配信を終了して、配信枠を返却します。\nよろしいですか？');" class="btn btn-danger btn-lg">配信を終了</a>
    <?php endif; ?>
  </div>
</div>

<?php include "../include/footer.php"; ?>
</body>
</html>