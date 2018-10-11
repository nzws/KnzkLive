<?php
require_once("../lib/bootloader.php");

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
    header("Location: ".u("live_manage"));
    exit();
}

$slot = getAbleSlot();
if (!$slot) {
    http_response_code(503);
    exit("ERR:現在、配信枠が不足しています。");
}

if (isset($_POST["title"]) && isset($_POST["description"]) && isset($_POST["privacy_mode"])) {
    if ($_POST["privacy_mode"] != "1" && $_POST["privacy_mode"] != "2" && $_POST["privacy_mode"] != "3") {
        http_response_code(500);
        exit();
    }
    $random = bin2hex(random_bytes(32));

    $mysqli = db_start();
    $stmt = $mysqli->prepare("INSERT INTO `live` (`id`, `name`, `description`, `user_id`, `slot_id`, `created_at`, `ended_at`, `is_live`, `ip`, `token`, `privacy_mode`, `viewers_count`) VALUES (NULL, ?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, '1', ?, ?, ?, '0');");
    $stmt->bind_param('sssssss', s($_POST["title"]), s($_POST["description"]), $my["id"], $slot, $_SERVER["REMOTE_ADDR"], $random, s($_POST["privacy_mode"]));
    $stmt->execute();
    $stmt->close();
    $mysqli->close();

    $mysqli = db_start();
    $stmt = $mysqli->prepare("SELECT * FROM `live` WHERE (is_live = 1 OR is_live = 2) AND user_id = ?;");
    $stmt->bind_param("s", $my["id"]);
    $stmt->execute();
    $row = db_fetch_all($stmt);
    $stmt->close();
    $mysqli->close();
    setUserLive($row[0]["id"]);
    setSlot($slot, 1);
    header("Location: ".u("live_manage"));
    exit();
}
?>
<!doctype html>
<html lang="ja">
<head>
    <?php include "../include/header.php"; ?>
    <title>配信を始める - <?=$env["Title"]?></title>
</head>
<body>
<?php include "../include/navbar.php"; ?>
<div class="container">
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']?>">
        <div class="form-group">
            <label for="title">配信タイトル</label>
            <input type="text" class="form-control" id="title" name="title" aria-describedby="title_note" placeholder="タイトル" required>
            <small id="title_note" class="form-text text-muted">100文字以下</small>
        </div>

        <div class="form-group">
            <label for="description">配信の説明</label>
            <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
        </div>

        <div class="form-check">
            <input class="form-check-input" type="radio" name="privacy_mode" id="privacy_mode1" value="1" checked>
            <label class="form-check-label" for="privacy_mode1">
                公開<br>
                <small>トップページに表示され、誰でも視聴できます</small>
            </label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="privacy_mode" id="privacy_mode2" value="2">
            <label class="form-check-label" for="privacy_mode2">
                未収載<br>
                <small>トップページに表示されませんが、URLがあれば誰でも視聴できます</small>
            </label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="privacy_mode" id="privacy_mode3" value="3">
            <label class="form-check-label" for="privacy_mode3">
                非公開<br>
                <small>トップページに表示されず、視聴にはログインが必要です</small><br>
                <small>* あなたのフォロワーでなくても、KnzkLiveにログインしていれば視聴できます</small>
            </label>
        </div>

        <div class="form-group form-check">
            <input type="checkbox" class="form-check-input" id="term" required>
            <label class="form-check-label" for="term"><a href="<?=u("terms")?>" target="_blank">利用規約・ガイドライン</a>に同意する</label>
        </div>

        <button type="submit" class="btn btn-primary">配信枠を取得</button>
    </form>
</div>

<?php include "../include/footer.php"; ?>
</body>
</html>