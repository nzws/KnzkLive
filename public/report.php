<?php
require_once "../lib/bootloader.php";
$my = getMe();
if (!$my) {
    http_response_code(403);
    exit("ERR:ログインしてください。");
}

if (isset($_GET["liveid"])) {
    $live = getLive($_GET["liveid"]);
    if (!$live) {
        exit("err: 配信が存在しません。");
    }
    $liveUser = getUser($live["user_id"]);
}

if (isset($_POST["body"])) {
    $id = bin2hex(openssl_random_pseudo_bytes(12));

    $data = [
        "content" => $_POST["body"],
        "embeds" => [
            [
                "title" => "新しい通報",
                "description" => $id,
                "url" => liveUrl($live["id"]),
                "color" => "16717636",
                "author" => [
                    "name" => "KnzkLive",
                    "url" => "https://live.knzk.me",
                    "icon_url" => "https://github.com/KnzkDev.png"
                ],
                "fields" => [
                    [
                        "name" => "IP",
                        "value" => $_SERVER["REMOTE_ADDR"]
                    ],
                    [
                        "name" => "UA",
                        "value" => $_SERVER['HTTP_USER_AGENT']
                    ],
                    [
                        "name" => "Reported by",
                        "value" => $my["acct"]
                    ]
                ]
            ]
        ]
    ];

    if (!sendToDiscord($data)) {
        exit("error: 送信に失敗しました。");
    }
}
?>
<!doctype html>
<html lang="ja">
<head>
    <?php include "../include/header.php"; ?>
    <title>通報 - <?=$env["Title"]?></title>
</head>
<body>
<?php include "../include/navbar.php"; ?>
<div class="container">
    <?php if (isset($id)) : ?>
        <div class="alert alert-success" role="alert">
            <b>送信しました。</b> お問い合わせID: <?=$id?>
        </div>
    <?php else : ?>
    <div class="box">
        <h4>通報フォーム</h4>
        <div class="col-md-7">
            <form method="post" id="knzkpoint">
                <input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']?>">

                <?php if (isset($live)) : ?>
                <p>
                    通報する配信: 「<b><?=$live["name"]?></b>」 by <?=$liveUser["name"]?>
                </p>
                <?php endif; ?>

                <div class="form-group">
                    <label for="body">本文 (なにをどうしてほしいのか具体的にお願いします)</label>
                    <textarea class="form-control" name="body" placeholder="必須, 1000文字まで" maxlength="1000" required></textarea>
                </div>
                <button class="btn btn-primary btn-block" type="submit" onclick="return confirm('送信します。よろしいですか？')">送信</button>
            </form>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include "../include/footer.php"; ?>
</body>
</html>
