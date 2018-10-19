<?php require_once("../lib/bootloader.php"); ?>
<!doctype html>
<html lang="ja">
<head>
  <?php include "../include/header.php"; ?>
  <title>ヘルプ・お問い合わせ - KnzkLive</title>
</head>
<body>
<?php include "../include/navbar.php"; ?>
<div class="container">
  <h3>ヘルプ</h3>
</div>
<div class="container" id="help1">
  <h4>外部インスタンスから投稿したコメントが表示できない</h4>
  KnzkLiveでは <b><?=$env["masto_login"]["domain"]?>から取得した特定ハッシュタグのタイムライン</b> と、 <b>独自のコメントサーバ</b> の2つからコメントとして取得しています。<br>
  そのため、配信者が許可している場合は外部インスタンスのアカウントからのトゥートをコメントとして取得する事ができますが、<br>
  <b><?=$env["masto_login"]["domain"]?>のアカウントにフォローされていない、サイレンスやサスペンドされているアカウントは取得できません</b>のでご注意ください。<br>
  なお、<?=$env["masto_login"]["domain"]?>に自動フォロバ機能付きのボットがありますのでお困りの際はご利用ください。<a href="https://knzk.me/@EffectBot" target="_blank">@EffectBot</a>
</div>
<hr>
<div class="container" id="contact">
  <h3>お問い合わせ</h3>
  KnzkLiveに関するお問い合わせは下記までお願いします。
  <ul>
    <li>開発者のMastodonアカウント: <a href="https://knzk.me/@y" target="_blank">@y@knzk.me</a>
    <li>GitHubリポジトリ: <a href="https://github.com/KnzkDev/KnzkLive" target="_blank">KnzkDev/KnzkLive</a>
  </ul>
</div>

<?php include "../include/footer.php"; ?>
</body>
</html>