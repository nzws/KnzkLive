<?php
require_once("../../lib/bootloader.php");
$my = getMe();
if (!$my) {
  http_response_code(403);
  exit("ERR:ログインしてください。");
}

if ($_POST) {
  if (mb_strlen($_POST["comment"]) > 500) exit("ERR:文字数制限オーバー");
  if (intval($_POST["point"]) > $my["point_count"] || !$_POST["point"] || intval($_POST["point"]) <= 0 || !is_numeric($_POST["point"])) exit("ERR:ポイントが不正です。");
  $u = getUser($_POST["user"], "acct");
  if ($u["id"] === $my["id"]) exit("ERR:自分自身には送信できません");
  if ($u) {
    $n = add_point($my["id"], $_POST["point"] * -1, "user", $u["acct"] . "にプレゼント");
    if ($n) {
      $o = add_point($u["id"], $_POST["point"], "user", $my["acct"] . "からのプレゼント  コメント: " . s($_POST["comment"]));
      if ($o) header("Location: " . u("settings"));
      else exit("例外エラー");
    }
  } else {
    exit("ERR:ユーザーが見つかりません。");
  }
}
?>
<!doctype html>
<html lang="ja">
<head>
  <?php include "../../include/header.php"; ?>
  <title>ポイントをプレゼント - <?=$env["Title"]?></title>
</head>
<body>
<?php include "../../include/navbar.php"; ?>
<div class="container">
  <div class="box">
    <h4>ポイントをプレゼント</h4>
    <div class="col-md-7">
      <p>
        <b>現在の保有ポイント: <span class="badge badge-success"><?=$my["point_count"]?>KP</span></b>
      </p>
      <form method="post" id="knzkpoint">
        <input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']?>">
        <div class="form-group">
          <label for="point">プレゼントするポイント数</label>
          <div class="input-group">
            <input type="number" class="form-control" max="<?=$my["point_count"]?>" min="1" id="point" name="point"  aria-describedby="kp" required>
            <div class="input-group-append">
              <span class="input-group-text" id="kp">KP</span>
            </div>
          </div>
          <small id="emailHelp" class="form-text text-muted">1 ~ <?=$my["point_count"]?>KPまで送信できます</small>
        </div>
        <div class="form-group">
          <label for="user">プレゼントするユーザー</label>
          <input type="email" class="form-control" id="user" name="user" placeholder="knzk@knzk.me" required>
          <small id="emailHelp" class="form-text text-muted">KnzkLiveに登録しているユーザーが対象です / Twitterアカウントは @twitter.com を、 <?=$env["masto_login"]["domain"]?> アカウントは @<?=$env["masto_login"]["domain"]?> を入力してください。</small>
        </div>
        <div class="form-group">
          <label for="comment">コメント</label>
          <textarea class="form-control" name="comment" placeholder="500文字まで" maxlength="500"></textarea>
        </div>
      </form>
      <button class="btn btn-primary" onclick="p_submit()" id="submit_bt">送信</button>
    </div>
  </div>
  <hr>
</div>

<?php include "../../include/footer.php"; ?>
<script>
  let now_point = <?=$my["point_count"]?>;
  function p_submit() {
    const bt = document.getElementById("submit_bt");
    const user = document.getElementById("user").value;
    if (now_point < document.getElementById("point").value) {
      alert("保有ポイント以上をプレゼントする事はできません。");
      return false;
    }
    bt.disabled = true;
    bt.textContent = "やってます...";
    fetch('<?=u("api/client/get_acct_name")?>?acct=' + user, {
      method: 'GET',
      credentials: 'include',
    }).then(function(response) {
      if (response.ok) {
        return response.json();
      } else {
        throw response;
      }
    }).then(function(json) {
      if (json["error"]) {
        alert("Error: " + json["error"]);
        bt.disabled = false;
        bt.textContent = "送信";
        return false;
      }
      if (confirm(`「${json["name"]}」(${json["acct"]})に送信してもよろしいですか？`)) {
        document.getElementById("knzkpoint").submit();
      } else {
        bt.disabled = false;
        bt.textContent = "送信";
      }
    }).catch(function(error) {
      alert("例外エラーが発生");
      console.error(error);
    });
  }
</script>
</body>
</html>
