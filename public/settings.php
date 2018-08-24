<?php
require_once("../lib/initload.php");
$my = getMe();
if (!$my) {
    http_response_code(403);
    exit("ERR:ログインしてください。");
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
  <title>ユーザー設定 - <?=$env["Title"]?></title>
</head>
<body>
  <?php include "../include/navbar.php"; ?>
  <div class="container">
    <p>
      <h4>プロフィール設定</h4>
      Mastodonで変更した後、KnzkLiveでログアウト→ログインすると更新されます。
    </p>
<?php if ($my["isLive"]) : ?>
    <p>
      <h4>配信者設定</h4>
      <p>* この設定は過去、未来全ての配信に適用されます。</p>
      <div class="custom-control custom-checkbox">
        <input type="checkbox" class="custom-control-input" id="conf_toot">
        <label class="custom-control-label" for="conf_toot">
          KnzkLive外で投稿されたトゥートはコメントに表示しない <a href="javascript:alert('KnzkLiveではMastodonに投稿した #knzklive_(配信ID) タグのトゥートをコメントとして読み込むため、タグを付けて別クライアントでトゥートしてもコメントとして読み込まれます。荒らしなどがある場合は有効化してください。')">説明</a>
        </label>
      </div>
      <!--
      <div class="custom-control custom-checkbox">
        <input type="checkbox" class="custom-control-input" id="conf_joke_viewer">
        <label class="custom-control-label" for="conf_joke_viewer">
          ジョークコマンドの使用を視聴者に許可する <a href="javascript:alert('KnzkLiveではMastodonに投稿した #knzklive_(配信ID) タグのトゥートをコメントとして読み込むため、タグを付けて別クライアントでトゥートしてもコメントとして読み込まれます。荒らしなどがある場合は有効化してください。')">説明</a>
        </label>
      </div>
      -->
    </p>
<?php endif; ?>
  </div>

<?php include "../include/footer.php"; ?>
</body>
</html>