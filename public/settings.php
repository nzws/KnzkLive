<?php
require_once("../lib/bootloader.php");
$my = getMe();
if (!$my) {
  http_response_code(403);
  exit("ERR:ログインしてください。");
}

if ($_POST) {
  $my["misc"]["live_toot"] = !!$_POST["live_toot"];
  $my["misc"]["to_title"] = !!$_POST["to_title"];
  $my["misc"]["no_toot_default"] = !!$_POST["no_toot_default"];
  setConfig($my["id"], $my["misc"]);
}
?>
<!doctype html>
<html lang="ja">
<head>
  <?php include "../include/header.php"; ?>
  <title>ユーザー設定 - <?=$env["Title"]?></title>
</head>
<body>
<?php include "../include/navbar.php"; ?>
<form method="post">
  <input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']?>">
  <div class="container">
    <div class="box">
      <h4>プロフィール設定</h4>
      Mastodonで変更した後、KnzkLiveでログアウト→ログインすると更新されます。
    </div>
    <div class="box">
      <h4>コメント設定</h4>
      <div class="form-group">
        <div class="custom-control custom-checkbox">
          <input type="checkbox" class="custom-control-input" id="no_toot" name="no_toot_default" value="1" <?=($my["misc"]["no_toot_default"] ? "checked" : "")?>>
          <label class="custom-control-label" for="no_toot">
            「Mastodonに投稿しない」をデフォルトにする
          </label>
        </div>
      </div>
    </div>
    <?php if ($my["isLive"]) : ?>
      <div class="box">
        <h4>配信者設定</h4>
        <p>* この設定は過去、未来全ての配信に適用されます。</p>

        <div class="form-group">
          <div class="custom-control custom-checkbox">
            <input type="checkbox" class="custom-control-input" id="conf_to_title" name="to_title" value="1" <?=($my["misc"]["to_title"] ? "checked" : "")?>>
            <label class="custom-control-label" for="conf_to_title">
              配信枠取得の際に前回のタイトルと説明を予め記入する
            </label>
          </div>
        </div>
      </div>
    <?php else : ?>
      <div class="box">
        <h3>配信を始める</h3>
        サイト管理者より、配信権限を付与してもらう必要があります。<br>
        ご興味のある方は<a href="https://knzk.me/@y" target="_blank">nzws</a>までご連絡ください。(現状、知り合いの方に限らせて頂いています。)<br>
        なお、配信者は<?=$env["masto_login"]["domain"]?>のアカウントが必要です。
      </div>
    <?php endif; ?>
    <button type="submit" class="btn btn-primary">更新</button>
  </div>
</form>

<?php include "../include/footer.php"; ?>
</body>
</html>