<?php
require_once "../lib/bootloader.php";
$my = getMe();
if (!$my) {
    http_response_code(403);
    exit("ERR:ログインしてください。");
}

if (!$my["broadcaster_id"]) {
    http_response_code(403);
    exit("ERR:あなたには配信権限がありません。");
}

$list = get_all_blocking_user($my["id"]);
?>
<!doctype html>
<html lang="ja">
<head>
    <?php include "../include/header.php"; ?>
    <title>ユーザーブロック管理 - <?=$env["Title"]?></title>
</head>
<body>
<?php include "../include/navbar.php"; ?>
<div class="container">
    <div class="box">
        <h4>ユーザーブロック管理</h4>

        <button class="btn btn-danger btn-lg btn-block mt-2 mb-2" data-toggle="modal" data-target="#blockingModal">新規登録</button>

        <div class="table-responsive">
            <table class="table">
                <thead>
                <tr>
                    <th></th>
                    <th>登録日時</th>
                    <th>永続的?</th>
                    <th>視聴ブロック?</th>
                    <th>コマンド</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($list as $item) : ?>
                    <tr>
                        <td><?=$item["target_user_acct"]?></td>
                        <td><?=$item["created_at"]?></td>
                        <td><?=$item["is_permanent"] === 1 ? "はい" : "いいえ"?></td>
                        <td><?=$item["is_blocking_watch"] === 1 ? "はい" : "いいえ"?></td>
                        <td><a href="#" onclick="knzk.live.admin.removeBlocking('<?=$item["target_user_acct"]?>', this);return false">削除</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include "../include/live/add_blocking.php"; ?>
<?php include "../include/footer.php"; ?>
</body>
</html>
