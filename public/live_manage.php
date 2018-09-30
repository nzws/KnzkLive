<?php
require_once("../lib/bootloader.php");

$my = getMe();
if (!isset($my)) {
    http_response_code(403);
    exit("ERR:ログインしてください。");
}

if (!$my["liveNow"]) {
    header("Location: ".u("new"));
    exit();
}
$live = getLive($my["liveNow"]);
if (!isset($live)) {
    http_response_code(500);
    exit("ERR:問題が発生しました。管理者にお問い合わせください。");
}
$slot = getSlot($live["slot_id"]);

if (isset($_GET["mode"])) {
    if ($_SESSION['csrf_token'] != $_GET['t']) {
        http_response_code(403);
        exit("ERROR: CSRF Challenge is failed");
    }

    if ($_GET["mode"] == "shutdown") {
        setSlot($live["slot_id"], 0);
        setUserLive(0);

        $mysqli = db_start();
        $stmt = $mysqli->prepare("UPDATE `live` SET is_live = 0 WHERE id = ?;");
        $stmt->bind_param("s", $live["id"]);
        $stmt->execute();
        $stmt->close();
        $mysqli->close();

        header("Location: ".u());
        exit();
    }
}

$liveurl = (empty($_SERVER["HTTPS"]) ? "http://" : "https://") . $_SERVER["HTTP_HOST"] . u("live") . "?id=" . $live["id"];
?>
<!doctype html>
<html lang="ja">
<head>
    <?php include "../include/header.php"; ?>
    <title>配信を管理 - <?=$env["Title"]?></title>
</head>
<body>
<?php include "../include/navbar.php"; ?>
<div class="container">
    <div class="row">
        <div class="box col-md-8">
            <b>配信URL:</b><br>
            <div class="input-group">
                <input class="form-control" type="text" value="<?=$liveurl?>" readonly>
                <div class="input-group-append">
                    <a class="btn btn-primary" href="<?=$liveurl?>">Open</a>
                </div>
            </div>
        </div>
    </div>

    <div class="box">
        <b>配信サーバー情報:</b><br>
        <span class="text-danger">* このURLは漏洩すると第三者に配信を乗っ取られる可能性がありますので十分にご注意ください。</span><br>
        <div class="row">
            <div class="col-md-6">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text" id="url">URL</span>
                    </div>
                    <input type="text" class="form-control" aria-describedby="url" readonly placeholder="クリックで表示" onclick="window.prompt('URL *厳重管理', 'rtmp://<?=$slot["server_ip"]?>/live?token=<?=$live["token"]?>')">
                </div>
            </div>
            <div class="col-md-6">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text" id="key">ストリームキー</span>
                    </div>
                    <input type="text" class="form-control" aria-describedby="key" readonly value="<?=$live["id"]?>stream">
                </div>
            </div>
        </div>
    </div>

    <div class="box">
        <b>配信を終了:</b><br>
        <span class="text-danger">* このボタンはソフト側(OBSなど)で配信終了してからクリックしてください。</span><br>
        <a href="<?=u("live_manage")?>?mode=shutdown&t=<?=$_SESSION['csrf_token']?>" onclick="return confirm('配信を終了して、配信枠を返却します。\nよろしいですか？');" class="btn btn-danger btn-lg">配信を終了</a>
    </div>
</div>

<?php include "../include/footer.php"; ?>
</body>
</html>