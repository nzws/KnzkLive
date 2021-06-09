<?php
require_once "../../lib/bootloader.php";
$my = getMe();
if (!$my) {
    showError("ログインしてください。", 403);
}

if ($_POST) {
    if (mb_strlen($_POST["comment"]) > 500) {
        showError("文字数制限オーバー", 400);
    }
    if (!check_point_true($my["point_count"], $_POST["point"]) || $_POST["point"] <= 1) {
        showError("ポイント数が足りないか不正です。", 400);
    }

    $hash = create_ticket($my["id"], intval($_POST["point"] * 0.85), $_POST["comment"]);
    if (!$hash) {
        showError("内部エラーが発生しました (チケット)", 500);
    }
    $n = add_point($my["id"], $_POST["point"] * -1, "user", "チケット発行 チケットID: " . $hash);
    if (!$n) {
        showError("内部エラーが発生しました (ポイント)", 500);
    }

    $userCache = null;
    $my = getMe();
}
?>
<!doctype html>
<html lang="ja">
<head>
    <?php include "../../include/header.php"; ?>
    <title>ポイントのチケットを発行 - <?=$env["Title"]?></title>
</head>
<body>
<?php include "../../include/navbar.php"; ?>
<div class="container">
    <?php if (isset($hash)) : ?>
        <div class="alert alert-success" role="alert">
            <b>チケットを発行しました！</b> チケットURL: https://<?=$env["domain"]?><?=u("ticket")?>?id=<?=$hash?><br>
            チケットURLは大切に保管しておいてください。
        </div>
    <?php endif; ?>
    <div class="box">
        <h4>ポイントのチケットを発行</h4>
        <div class="col-md-7">
            <p>
                <b>現在の保有ポイント: <span class="badge badge-success"><?=$my["point_count"]?>KP</span></b>
            </p>
            <form method="post" id="knzkpoint">
                <input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']?>">
                <div class="form-group">
                    <label for="point">チケットにするポイント数</label>
                    <div class="input-group">
                        <input type="number" class="form-control" max="<?=$my["point_count"]?>" min="2" id="point" name="point"  aria-describedby="kp" required onkeyup="calcPoint(this)">
                        <div class="input-group-append">
                            <span class="input-group-text" id="kp">KP</span>
                        </div>
                    </div>
                    <small id="emailHelp" class="form-text text-muted">2 ~ <?=$my["point_count"]?>KP</small>
                    <small class="text-warning">15%が手数料として回収されます。 (<span id="fee">0</span>KP * 0.85 = <b id="fee_result">0</b>KP)</small>
                </div>
                <div class="form-group">
                    <label for="comment">コメント</label>
                    <textarea class="form-control" name="comment" placeholder="500文字まで" maxlength="500"></textarea>
                </div>
                <button class="btn btn-primary" type="submit">送信</button>
            </form>
        </div>
    </div>
    <hr>
</div>

<?php include "../../include/footer.php"; ?>
<script>
    function calcPoint(obj) {
        const base = document.getElementById('fee');
        const result = document.getElementById('fee_result');
        const point = parseInt(obj.value);

        if (point) {
            base.textContent = point;
            result.textContent = parseInt(point * 0.85);
        }
    }
</script>
</body>
</html>
