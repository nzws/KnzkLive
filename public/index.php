<?php
require_once("../lib/bootloader.php");
$lives = getAllLive();
if ($lives[0]["id"]) { //1人用としか考えてない頭わるわる仕様なので後でなんとかする
  header("Location: ".liveUrl($lives[0]["id"]));
  exit();
}
?>
<!doctype html>
<html lang="ja">
<head>
    <?php include "../include/header.php"; ?>
    <title>トップ - <?=$env["Title"]?></title>
    <style>
        .nav-container {
            margin: 10px auto 0;
        }
        .navbar {
            border-radius: 5px 5px 0 0;
        }
    </style>
</head>
<body>
<?php include "../include/navbar.php"; ?>
<div class="top_lists">
    <?php if ($lives[0]) : ?>
        <div class="container">

        </div>
    <?php else : ?>
        <div class="no">
            <h4>現在、生放送中の配信はありません</h4>
        </div>
    <?php endif; ?>
</div>
<div class="container">

</div>

<?php include "../include/footer.php"; ?>
</body>
</html>