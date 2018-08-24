<?php
require_once("../lib/initload.php");
$lives = getAllLive();
?>
<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO"
    crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
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