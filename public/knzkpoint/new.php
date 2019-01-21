<?php
require_once("../../lib/bootloader.php");
$my = getMe();
if (!$my) {
  http_response_code(403);
  exit("ERR:ログインしてください。");
}

if ($_POST) {
  if (mb_strlen($_POST["comment"]) > 500) exit("ERR:文字数制限オーバー");
  if (intval($_POST["point"]) > $my["point_count"] || !$_POST["point"] || intval($_POST["point"]) <= 0 || !is_numeric($_POST["point"])) exit("ERR:ポイントが足りないか不正です。");

  $hash = create_ticket($my["id"], $_POST["point"], $_POST["comment"]);
  if (!$hash) exit("作成エラー");
  $n = add_point($my["id"], $_POST["point"] * -1, "user", "チケット発行 チケットID: " . $hash);
  if (!$n) exit("作成エラー (管理者にお問い合わせください)");

  $userCache = null;
  $my = getMe();
}
?>
<!doctype html>
<html lang="ja">
<head>
  <?php include "../../include/header.php"; ?>
  <title>ポイントのチケットを発行 - <?=$env["Title"]?></title>
</head>
<body>
<?php include "../../include/navbar.php"; ?>
<div class="container">
  <?php if (isset($hash)) : ?>
    <div class="alert alert-success" role="alert">
      <b>チケットを発行しました！</b> チケットURL: https://<?=$env["domain"]?><?=u("ticket")?>?id=<?=$hash?><br>
      チケットURLは大切に保管しておいてください。
    </div>
  <?php endif; ?>
  <div class="box">
    <h4>ポイントのチケットを発行</h4>
    <div class="col-md-7">
      <p>
        <b>現在の保有ポイント: <span class="badge badge-success"><?=$my["point_count"]?>KP</span></b>
      </p>
      <form method="post" id="knzkpoint">
        <input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']?>">
        <div class="form-group">
          <label for="point">チケットにするポイント数</label>
          <div class="input-group">
            <input type="number" class="form-control" max="<?=$my["point_count"]?>" min="1" id="point" name="point"  aria-describedby="kp" required>
            <div class="input-group-append">
              <span class="input-group-text" id="kp">KP</span>
            </div>
          </div>
          <small id="emailHelp" class="form-text text-muted">1 ~ <?=$my["point_count"]?>KPまで送信できます</small>
        </div>
        <div class="form-group">
          <label for="comment">コメント</label>
          <textarea class="form-control" name="comment" placeholder="500文字まで" maxlength="500"></textarea>
        </div>
        <button class="btn btn-primary" type="submit">送信</button>
      </form>
    </div>
  </div>
  <hr>
</div>

<?php include "../../include/footer.php"; ?>
</body>
</html>
