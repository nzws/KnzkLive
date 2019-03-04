<?php
require __DIR__ . "/../config.php";

function assets() {
  global $env;
  return (empty($env["assets_url"]) ? $env["RootUrl"] : $env["assets_url"]);
}
?>
<!DOCTYPE html>
<html data-page="errorpage">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Error / エラー - KnzkLive</title>
  <link rel="stylesheet" href="<?=assets()?>bundle/bundle.css?t=<?=filemtime(__DIR__ . "/../public/bundle/bundle.css")?>">
  <link rel="shortcut icon" type="image/x-icon" href="<?=assets()?>static/favicon.ico">
</head>
<body>
<img src="<?=assets()?>static/surprized_knzk.png"/>
<?php if (!empty($errortext)) : ?>
  <h2><?=$errortext?></h2>
<?php else : ?>
  <h1>We're sorry, but something went wrong.</h1>
  <h1>申し訳ありません。予期せぬエラーが発生しました。</h1>
<?php endif; ?>
<p>
  <a href="<?=$env["RootUrl"]?>">ホームに戻る / Back to home</a><br>
  KnzkLive
</p>
</body>
</html>
<?php exit(); ?>
