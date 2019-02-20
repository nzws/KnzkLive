<?php
require_once("../lib/bootloader.php");

$my = getMe();
if (!isset($my)) {
  http_response_code(403);
  exit("ERR:ログインしてください。");
}

if (!$my["live_current_id"]) {
  header("Location: ".u("new"));
  exit();
}
$live = getLive($my["live_current_id"]);
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
    if ($live["is_live"] === 2) {
      disconnectClient($live);
    }
    end_live($live["id"]);
    header("Location: ".u());

    exit();
  }
}

if (isset($_POST["type"])) {
  if ($_POST["type"] == "start" && $_POST["start_post"] > 0 && $_POST["start_post"] < 5 && $live["is_started"] != 1) {
    $mysqli = db_start();
    $stmt = $mysqli->prepare("UPDATE `live` SET is_started = 1, created_at = CURRENT_TIMESTAMP WHERE id = ?;");
    $stmt->bind_param('s', $live["id"]);
    $stmt->execute();
    $stmt->close();
    $mysqli->close();
    $live = getLive($live["id"]);

    if ($_POST["start_post"] < 4) {
      $visibility = $_POST["start_post"] == 1 ? "public" :
        ($_POST["start_post"] == 2 ? "unlisted" :
          ($_POST["start_post"] == 3 ? "private" : ""));
      postLiveStart($live, $_POST["start_push"], $visibility);
    }
    if (!empty($my["misc"]["webhook_url"]) && isset($_POST["start_push"])) {
      postWebHook($live);
    }
  } elseif ($_POST["type"] == "edit") {
    if (!$live["misc"]["is_sensitive"] && isset($_POST["sensitive"])) update_realtime_config("sensitive", true, $live["id"]);
    $title = s($_POST["title"]);
    $desc = s($_POST["description"]);
    $live["misc"]["is_sensitive"] = isset($_POST["sensitive"]);
    $misc = json_encode($live["misc"]);

    $mysqli = db_start();
    $stmt = $mysqli->prepare("UPDATE `live` SET name = ?, description = ?, misc = ? WHERE id = ?;");
    $stmt->bind_param('ssss', $title, $desc, $misc, $live["id"]);
    $stmt->execute();
    $stmt->close();
    $mysqli->close();
    $live = getLive($live["id"]);
  }
}

$liveurl = liveUrl($live["id"]);
$comurl = (empty($_SERVER["HTTPS"]) ? "http://" : "https://") . $_SERVER["HTTP_HOST"] . u("api/client/comment_viewer") . "?id=" . $live["id"];

$vote = loadVote($live["id"]);
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
  <a href="?" class="btn btn-info btn-sm">再読込</a>
</div>
<?php if ($live["is_started"] == "0") : ?>
  <div class="container">
    <div class="box">
      <div class="alert alert-info" role="alert">
        <form method="post" action="?">
          <input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']?>">
          <input type="hidden" name="type" value="start">

          <div class="row">
            <div class="col-md-6">
              <div class="form-group row">
                <label for="start_post" class="col-sm-7 col-form-label">
                  配信開始投稿<br>
                  <small>@KnzkLiveNotificationに投稿します</small>
                </label>
                <div class="col-sm-5">
                  <select class="form-control" id="start_post" name="start_post">
                    <option value="1">公開</option>
                    <option value="2">未収載</option>
                    <option value="3">非公開</option>
                    <option value="4">なし</option>
                  </select>
                </div>
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <div class="custom-control custom-checkbox">
                  <input type="checkbox" class="custom-control-input" id="start_push" name="start_push" value="1" checked>
                  <label class="custom-control-label" for="start_push">
                    KnzkAppにプッシュ通知する<br>
                    <small>(配信開始投稿を有効にしている場合)</small>
                  </label>
                </div>
              </div>
              <?php if (!empty($my["misc"]["webhook_url"])) : ?>
              <div class="form-group">
                <div class="custom-control custom-checkbox">
                  <input type="checkbox" class="custom-control-input" id="start_webhook" name="start_webhook" value="1" checked>
                  <label class="custom-control-label" for="start_webhook">
                    WebHookに送信する
                  </label>
                </div>
              </div>
              <?php endif; ?>
            </div>
          </div>
          <button type="submit"
                  onclick="return confirm('配信を開始します。\nよろしいですか？');"
                  class="btn btn-info btn-lg btn-block">
            :: 配信を開始 ::
          </button>
        </form>
      </div>
    </div>
  </div>
  <hr>
<?php else : ?>
<?php endif; ?>

<div class="container">
  <div class="box">
    <form method="post">
      <input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']?>">
      <input type="hidden" name="type" value="edit">
      <div class="form-group">
        <label for="title">配信タイトル</label>
        <input type="text" class="form-control" id="title" name="title" aria-describedby="title_note" placeholder="タイトル" required value="<?=$live["name"]?>">
        <small id="title_note" class="form-text text-muted">100文字以下</small>
      </div>

      <div class="form-group">
        <label for="description">配信の説明</label>
        <textarea class="form-control" id="description" name="description" rows="4" required><?=$live["description"]?></textarea>
      </div>

      <div class="form-group form-check">
        <input type="checkbox" class="form-check-input" id="sensitive" name="sensitive" value="1" <?=($live["misc"]["is_sensitive"] ? "checked" : "")?>>
        <label class="form-check-label" for="sensitive">
          センシティブな配信としてマークする<br>
          <small>途中から有効にすると一度視聴者全員のプレイヤーが停止し警告がポップアップで表示されます</small>
        </label>
      </div>

      <button type="submit" class="btn btn-primary">更新</button>
    </form>
  </div>
  <hr>

  <div class="box">
    <div class="row">
      <div class="col-md-6">
        <b>配信URL:</b><br>
        <div class="input-group">
          <input class="form-control" type="text" value="<?=$liveurl?>" readonly>
          <div class="input-group-append">
            <button class="btn btn-secondary copy" type="button" data-clipboard-text="<?=$liveurl?>">コピー</button>
            <a class="btn btn-primary" href="<?=$liveurl?>" target="_blank">Open</a>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <b>コメビュURL:</b><br>
        <div class="input-group">
          <input class="form-control" type="text" value="<?=$comurl?>" readonly>
          <div class="input-group-append">
            <button class="btn btn-secondary copy" type="button" data-clipboard-text="<?=$comurl?>">コピー</button>
          </div>
        </div>
      </div>
    </div>
  </div>
  <hr>

  <div class="box">
    <b>配信サーバー情報:</b><br>
    <small>Windows・OBS環境の方は<a href="https://github.com/KnzkDev/KnzkLiveOBSOpener" target="_blank">KnzkLiveOBSOpener</a>で自動設定ができます。</small><br>
    <span class="text-danger">* この情報は漏洩すると第三者に配信を乗っ取られる可能性がありますので十分にご注意ください。</span><br>
    <div class="row">
      <div class="col-md-6">
        <div class="input-group">
          <div class="input-group-prepend">
            <span class="input-group-text" id="url">URL</span>
          </div>
          <input type="text" class="form-control" aria-describedby="url" readonly value="rtmp://<?=$slot["server_ip"]?>/live">
          <div class="input-group-append">
            <button class="btn btn-secondary copy" type="button" data-clipboard-text="rtmp://<?=$slot["server_ip"]?>/live">コピー</button>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="input-group">
          <div class="input-group-prepend">
            <span class="input-group-text" id="key">ストリームキー</span>
          </div>
          <input type="text" class="form-control" aria-describedby="key" readonly placeholder="クリックで表示" onclick="window.prompt('ストリームキー', '<?=$live["id"]?>stream?token=<?=$live["token"]?>')">
          <div class="input-group-append">
            <button class="btn btn-secondary copy" type="button" data-clipboard-text="<?=$live["id"]?>stream?token=<?=$live["token"]?>">コピー</button>
          </div>
        </div>
      </div>
    </div>
  </div>
  <hr>

  <div class="box">
    <div class="row">
      <div class="col-md-6">
        <b>配信をシェア(配信者用):</b><br>
        <div class="btn-group btn-block mt-3" role="group">
          <button class="btn btn-primary disabled">Mastodon</button>
          <a href="https://<?=$env["masto_login"]["domain"]?>/share?text=<?=urlencode("#KnzkLive で配信中！\n{$live["name"]}\n{$liveurl}\n\nコメントタグ: #".liveTag($live))?>" target="_blank" class="btn btn-primary">標準</a>
          <a href="https://<?=$env["masto_login"]["domain"]?>/share?text=<?=urlencode("{$liveurl}\n{$liveurl}\n{$liveurl}")?>" target="_blank" class="btn btn-primary">神崎</a>
        </div>
        <div class="btn-group btn-block" role="group">
          <a href="https://twitter.com/intent/tweet?url=<?=urlencode($liveurl)?>&text=<?=urlencode("{$live["name"]} - #KnzkLive で配信中！ #".liveTag($live))?>" target="_blank" class="btn btn-info">Twitterで投稿</a>
        </div>
      </div>
      <div class="col-md-6">
        <a href="<?=u("live_manage")?>?mode=shutdown&t=<?=$_SESSION['csrf_token']?>" onclick="return confirm('配信を終了して、配信枠を返却します。\nよろしいですか？');" class="btn btn-danger btn-lg btn-block">配信を終了</a>
      </div>
    </div>
  </div>
</div>

<?php include "../include/footer.php"; ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.0/clipboard.min.js"></script>
<script>
  <?php if (isset($_GET["new"]) && $my["misc"]["auto_open_start"]) : ?>
  window.open('<?=$liveurl?>');
  <?php endif; ?>
  window.onload = function () {
    const clipboard = new ClipboardJS('.copy');
  }
</script>
</body>
</html>
