<?php
require_once("../lib/bootloader.php");
$my = getMe();
if (!$my) {
  http_response_code(403);
  exit("ERR:ログインしてください。");
}

if (isset($_GET["id"]) && isset($_POST["use"])) {
  $n = use_ticket($my["id"], $_GET["id"]);
  if ($n) header("Location: " . u("settings"));
  else exit("例外エラー");
} elseif (isset($_GET["id"])) {
  $t = get_ticket($_GET["id"]);
  if (!$t) exit("ERR: チケットが存在しません");
  $u = getUser($t["user_id"]);
}
?>
<!doctype html>
<html lang="ja">
<head>
  <?php include "../include/header.php"; ?>
  <title>チケットを使用 - <?=$env["Title"]?></title>
</head>
<body>
<?php include "../include/navbar.php"; ?>
<div class="container">
  <div class="box">
    <h4>チケットを使用</h4>
    <div class="col-md-7">
      <p>
        <b>現在の保有ポイント: <span class="badge badge-success"><?=$my["point_count"]?>KP</span></b>
      </p>
      <?php if (isset($_GET["id"])) : ?>
        <b><?=s($u["name"])?> (<?=s($u["acct"])?>)</b> さんのチケットを使用してもよろしいですか？<br>
        <b><?=s($t["point"])?>KP</b>があなたの残高に追加されます。
        <form method="post">
          <input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']?>">
          <input type="hidden" name="use" value="1">
          <button class="btn btn-success" type="submit">使用</button>
        </form>
      <?php else : ?>
        <form method="get">
          <div class="form-group">
            <label for="id">チケットID</label>
            <div class="input-group">
              <input type="text" class="form-control" name="id" required>
            </div>
            <small class="form-text text-muted">/ticket?id=~~</small>
          </div>
          <button class="btn btn-primary" type="submit">送信</button>
        </form>
      <?php endif; ?>
    </div>
  </div>
  <hr>
</div>

<?php include "../include/footer.php"; ?>
</body>
</html>
