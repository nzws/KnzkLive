<?php
require_once("../lib/bootloader.php");
$my = getMe();
if (!$my) showError('ログインしてください。', 403);
if (!$my["broadcaster_id"]) showError('あなたには配信権限がありません。', 403);

if (!empty($_POST)) {
  $name = s($_POST["word"]);
  if (!checkV($name, 1, 20)) showError('バリデーションエラー: name', 400);
  $point = intval($_POST["point"]);
  if (!($point >= 0 && $point <= 10000)) showError('バリデーションエラー: point', 400);
  $_POST["type"] = s($_POST["type"]);

  if ($_POST["type"] === "voice") {
    $able_item = null;
    $able_comment = null;
  } elseif ($_POST["type"] === "emoji") {
    if (!ctype_alnum($name)) showError("バリデーションエラー: 英数字", 400);
    $able_item = isset($_POST["emoji_type_item"]) && $_POST["emoji_type_item"] == 1 ? 1 : 0;
    $able_comment = isset($_POST["emoji_type_comment"]) && $_POST["emoji_type_comment"] == 1 ? 1 : 0;
  } else {
    showError("バリデーションエラー: type", 400);
  }

  $s3 = uploadFlie($_FILES["file"], $_POST["type"], $my["id"]);
  if (!$s3["success"]) showError($s3["error"], 500);

  $mysqli = db_start();
  $stmt = $mysqli->prepare("INSERT INTO `items` (`type`, `user_id`, `name`, `point`, `file_name`, `able_item`, `able_comment`) VALUES (?, ?, ?, ?, ?, ?, ?);");
  $stmt->bind_param('sssssss', $_POST["type"], $my["id"], $name, $point, $s3["file_name"], $able_item, $able_comment);
  $stmt->execute();
  $err = $stmt->error;
  $stmt->close();
  $mysqli->close();

  if ($err) {
    deleteFile($s3["file_name"], $_POST["type"]);
    showError('DB登録エラー', 500);
  }
}
?>
<!doctype html>
<html lang="ja" data-page="live_manage_items">
<head>
  <?php include "../include/header.php"; ?>
  <title>アイテム管理 - <?=$env["Title"]?></title>
</head>
<body>
<?php include "../include/navbar.php"; ?>
<div class="container">
  <nav>
    <div class="nav nav-tabs" id="nav-tab">
      <a class="nav-item nav-link active" id="se-tab" data-toggle="tab" href="#se">SE</a>
      <a class="nav-item nav-link" id="emoji-tab" data-toggle="tab" href="#emoji">絵文字</a>
    </div>
  </nav>
  <div class="tab-content" id="nav-tabContent">
    <div class="tab-pane fade show active" id="se">
    <h4>カスタムSE管理</h4>
    あなたの配信上のアイテムで使用できるSEを追加できます。<br>
    リスナーがアイテムよりSEを投下すると、リスナー全員にその音声が再生されます。<br>
    また、投下に必要なポイントの一定割合が配信者に還元されます。<br>
    (* ミュート中は再生されません。)

    <form method="post" class="mt-4 mb-4 col-md-7" enctype="multipart/form-data">
      <input type="hidden" name="type" value="voice">
      <input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']?>">

      <div class="form-group">
        <label>音声ファイル</label>
        <input type="file" name="file" required>
        <small class="form-text text-muted">mp3, wavファイルがアップロードできます・<?=ini_get('upload_max_filesize')?>まで</small>
      </div>

      <div class="form-group">
        <label>ボイス名</label>
        <input class="form-control" type="text" name="word" required>
        <small class="form-text text-muted">20文字まで</small>
      </div>

      <div class="form-group">
        <label>投下に必要なポイント</label>
        <div class="input-group">
          <input type="number" class="form-control" value="1" name="point" aria-describedby="kp" required>
          <div class="input-group-append">
            <span class="input-group-text" id="kp">KP</span>
          </div>
        </div>
        <small class="form-text text-muted">0~10000KPまで</small>
      </div>
      <button class="btn btn-primary btn-block" type="submit">追加</button>
    </form>

    <div class="table-responsive">
      <table class="table">
        <thead>
        <tr>
          <th>ボイス名</th>
          <th>ポイント</th>
          <th>コマンド</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach (getItems($my["id"], 'voice') as $item) : ?>
          <tr id="voice_<?=$item["id"]?>">
            <td><?=$item["name"]?></td>
            <td><?=$item["point"]?></td>
            <td><a href="#" onclick="knzk.settings.items.playVoice('<?=$item["file_name"]?>');return false">再生</a> / <a href="#" onclick="knzk.settings.items.remove('<?=$item["id"]?>', 'voice');return false">削除</a></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    </div>

    <div class="tab-pane fade" id="emoji">
      <h4>カスタム絵文字管理</h4>
      あなたの配信上のコメントやアイテムで使用できる絵文字を追加できます。

      <form method="post" class="mt-4 mb-4 col-md-7" enctype="multipart/form-data">
        <input type="hidden" name="type" value="emoji">
        <input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']?>">

        <div class="form-group">
          <label>絵文字ファイル</label>
          <input type="file" name="file" required>
          <small class="form-text text-muted">png, jpegファイルがアップロードできます・<?=ini_get('upload_max_filesize')?>まで</small>
        </div>

        <div class="form-group">
          <label>絵文字ID</label>
          <input class="form-control" type="text" name="word" required>
          <small class="form-text text-muted">英数字のみ, 20文字まで</small>
        </div>

        <div class="form-group">
          <label>投下に必要なポイント (アイテムのみ)</label>
          <div class="input-group">
            <input type="number" class="form-control" value="1" name="point" aria-describedby="kp" required>
            <div class="input-group-append">
              <span class="input-group-text" id="kp">KP</span>
            </div>
          </div>
          <small class="form-text text-muted">0~10000KPまで</small>
        </div>

        <div class="form-group">
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input" id="emoji_type_comment" name="emoji_type_comment" value="1" checked>
              <label class="custom-control-label" for="emoji_type_comment">
                コメントで使用可能
              </label>
            </div>
        </div>

        <div class="form-group">
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input" id="emoji_type_item" name="emoji_type_item" value="1" checked>
              <label class="custom-control-label" for="emoji_type_item">
                アイテムで使用可能
              </label>
            </div>
        </div>

        <button class="btn btn-primary btn-block" type="submit">追加</button>
      </form>

      <div class="table-responsive">
        <table class="table">
          <thead>
          <tr>
            <th>絵文字</th>
            <th>アイテム? (KP)</th>
            <th>コメント?</th>
            <th>コマンド</th>
          </tr>
          </thead>
          <tbody>
          <?php foreach (getItems($my["id"], 'emoji') as $item) : ?>
            <tr id="emoji_<?=$item["id"]?>">
              <td><img src="<?=$env["storage"]["root_url"]?>emoji/<?=$item["file_name"]?>" class="emoji"/> <?=$item["name"]?></td>
              <td><?=$item["able_item"] === 1 ? i("check") . " (" . $item["point"] . ")" : i("times")?></td>
              <td><?=$item["able_comment"] === 1 ? i("check") : i("times")?></td>
              <td><a href="#" onclick="knzk.settings.items.remove('<?=$item["id"]?>', 'emoji');return false">削除</a></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<?php include "../include/footer.php"; ?>
</body>
</html>
