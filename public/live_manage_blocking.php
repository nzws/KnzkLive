<?php
require_once("../lib/bootloader.php");
$my = getMe();
if (!$my) {
  http_response_code(403);
  exit("ERR:ログインしてください。");
}

if (!$my["is_broadcaster"]) {
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
            <td><?=$item["acct"]?></td>
            <td><?=$item["created_at"]?></td>
            <td><?=$item["is_permanent"] === 1 ? "はい" : "いいえ"?></td>
            <td><?=$item["is_blocking_watch"] === 1 ? "はい" : "いいえ"?></td>
            <td><a href="#" onclick="remove('<?=$item["target_user_id"]?>', '<?=$item["acct"]?>', this);return false">削除</a></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php include "../include/live/add_blocking.php"; ?>
<?php include "../include/footer.php"; ?>
<script>
function remove(id, acct, obj = null) {
  if (confirm(`「${acct}」のブロックを解除します。\nよろしいですか？`)) {
    fetch('<?=u("api/client/ngs/manage_users")?>', {
      headers: {'content-type': 'application/x-www-form-urlencoded'},
      method: 'POST',
      credentials: 'include',
      body: buildQuery({
        csrf_token: `<?=$_SESSION['csrf_token']?>`,
        type: 'remove',
        user_id: id
      })
    }).then(function(response) {
      if (response.ok) {
        return response.json();
      } else {
        throw response;
      }
    }).then(function(json) {
      if (json["error"]) {
        alert(json["error"]);
        return null;
      }
      if (json["success"]) {
        $(obj).parent().parent().remove();
      } else {
        alert("エラーが発生しました。データベースに問題が発生している可能性があります。");
      }
    }).catch(function(error) {
      console.error(error);
      alert("内部エラーが発生しました");
    });
  }
}
</script>
</body>
</html>
