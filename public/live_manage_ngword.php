<?php
require_once "../lib/bootloader.php";
$my = getMe();
if (!$my) {
    showError("ログインしてください。", 403);
}

if (!$my["broadcaster_id"]) {
    showError("あなたには配信権限がありません。", 403);
}
?>
<!doctype html>
<html lang="ja">
<head>
    <?php include "../include/header.php"; ?>
    <title>NGワード管理 - <?=$env["Title"]?></title>
</head>
<body>
<?php include "../include/navbar.php"; ?>
<div class="container">
    <div class="box">
        <h4>NGワード管理</h4>
        あなたの<b>全ての配信</b>で適用されます。<br>
        NGワードリストは、KnzkLiveの仕組み上、リスナーが一覧を取得できる可能性があります。<br>
        あなたが配信中の時にNGワードリストを更新した場合、リアルタイムでアップデートされます。
        <form method="post" class="mt-2 mb-2">
            <div class="input-group">
                <input class="form-control" type="text" id="word" placeholder="NGワードを追加...">
                <div class="input-group-append">
                    <button class="btn btn-primary" type="button" onclick="knzk.live.admin.updateNGWord(document.getElementById('word').value, true)">追加</button>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table">
                <tbody>
                <?php foreach ($my["ngwords"] as $item) : ?>
                    <tr><td><a href="#" onclick="knzk.live.admin.updateNGWord('<?=$item?>', false, this);return false">削除</a>　<?=$item?></td></tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<hr>
</div>

<?php include "../include/footer.php"; ?>
</body>
</html>
