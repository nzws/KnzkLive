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
          <button class="btn btn-primary" type="button" onclick="update(elemId('word').value, true)">追加</button>
        </div>
      </div>
    </form>

    <div class="table-responsive">
      <table class="table">
        <tbody>
        <?php foreach ($my["ngwords"] as $item) : ?>
          <tr><td><a href="#" onclick="update('<?=$item?>', false, this);return false">削除</a>　<?=$item?></td></tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<hr>
</div>

<?php include "../include/footer.php"; ?>
<script>
function update(name, type, obj = null) {
  const mode = type ? "追加" : "削除";
  if (confirm(`「${name}」を${mode}します。\nよろしいですか？`)) {
    fetch('<?=u("api/client/ngs/manage_words")?>', {
      headers: {'content-type': 'application/x-www-form-urlencoded'},
      method: 'POST',
      credentials: 'include',
      body: buildQuery({
        csrf_token: `<?=$_SESSION['csrf_token']?>`,
        type: type ? 'add' : 'remove',
        word: name
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
        if (type) {
          $("tbody").prepend(`<tr><td><a href="#" onclick="remove('${json["word"]}', false, this);return false">削除</a>　${json["word"]}</td></tr>`);
          elemId('word').value = "";
        } else $(obj).parent().parent().remove();
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
