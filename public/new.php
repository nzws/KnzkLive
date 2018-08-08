<?php
require_once("../lib/initload.php");

$my = getMe();
if (!$my) {
    http_response_code(403);
    exit("ERR:ログインしてください。");
}

if (!$my["isLive"]) {
    http_response_code(403);
    exit("ERR:あなたには配信権限がありません。");
}

if ($my["liveNow"]) {
    header("Location: live_manage");
    exit();
}

$slot = getAbleSlot();
if (!$slot) {
    http_response_code(503);
    exit("ERR:現在、配信枠が不足しています。");
}

if ($_POST["title"]) {
    $random = bin2hex(random_bytes(32));

    $mysqli = db_start();
    $stmt = $mysqli->prepare("INSERT INTO `live` (`id`, `name`, `user_id`, `slot_id`, `created_at`, `is_live`, `ip`, `users`, `token`) VALUES (NULL, ?, ?, ?, CURRENT_TIMESTAMP, '1', ?, '0', ?);");
    $stmt->bind_param('sssss', s($_POST["title"]), $my["id"], $slot, $_SERVER["REMOTE_ADDR"], $random);
    $stmt->execute();
    $stmt->close();
    $mysqli->close();

    $mysqli = db_start();
    $stmts = $mysqli->prepare("SELECT * FROM `live` WHERE is_live = 1 AND user_id = ?;");
    $stmts->bind_param("s", $my["id"]);
    $stmts->execute();
    $row = db_fetch_all($stmts);
    $stmts->close();
    $mysqli->close();
    
    setUserLive($row[0]["id"]);
    setSlot($slot, 1);
    header("Location: live_manage");
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
  <title>配信を始める - KnzkLive</title>
</head>
<body>
  <?php include "../include/navbar.php"; ?>
  <div class="container">
<form method="post">
    <input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']?>">
  <div class="form-group">
    <label for="title">配信タイトル</label>
    <input type="text" class="form-control" id="title" name="title" aria-describedby="title_note" placeholder="タイトル" required>
    <small id="title_note" class="form-text text-muted">100文字以下...？</small>
  </div>
  <div class="form-group form-check">
    <input type="checkbox" class="form-check-input" id="term" required>
    <label class="form-check-label" for="term">利用規約に同意</label>
  </div>
  <button type="submit" class="btn btn-primary">配信を開始</button>
</form>
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