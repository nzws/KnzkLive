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
    if ($live["is_live"] == 1) {
      setSlot($live["slot_id"], 0);
      setUserLive(0);

      if (setLiveStatus($live["id"], 0)) {
        if ($my["misc"]["viewers_max_concurrent"]) {
          if ($live["viewers_max_concurrent"] > $my["misc"]["viewers_max_concurrent"]) {
            $my["misc"]["viewers_max_concurrent"] = $live["viewers_max_concurrent"];
          }
        } else {
          $my["misc"]["viewers_max"] = 0;
          $my["misc"]["viewers_max_concurrent"] = $live["viewers_max_concurrent"];
        }
        $my["misc"]["viewers_max"] += $live["viewers_max"];
        setConfig($my["id"], $my["misc"]);

        $mysqli = db_start();
        $stmt = $mysqli->prepare("UPDATE `live` SET ended_at = CURRENT_TIMESTAMP, created_at = created_at WHERE id = ?;");
        $stmt->bind_param("s",$live["id"]);
        $stmt->execute();
        $stmt->close();
        $mysqli->close();
        header("Location: ".u());
    }
    } else echo "ERROR: setLiveStatus";

    exit();
  }
}

if (isset($_POST["type"])) {
  if ($_POST["type"] == "start" && $_POST["start_post"] > 0 && $_POST["start_post"] < 5 && $live["is_started"] != 1) {
    if ($_POST["start_post"] < 4) {
      $visibility = $_POST["start_post"] == 1 ? "public" :
        ($_POST["start_post"] == 2 ? "unlisted" :
          ($_POST["start_post"] == 3 ? "private" : ""));
      postLiveStart($live, $_POST["start_push"], $visibility);
    }
    $mysqli = db_start();
    $stmt = $mysqli->prepare("UPDATE `live` SET is_started = 1, created_at = CURRENT_TIMESTAMP WHERE id = ?;");
    $stmt->bind_param('s', $live["id"]);
    $stmt->execute();
    $stmt->close();
    $mysqli->close();
    $live = getLive($live["id"]);
  } elseif ($_POST["type"] == "edit") {
    $title = s($_POST["title"]);
    $desc = s($_POST["description"]);

    $mysqli = db_start();
    $stmt = $mysqli->prepare("UPDATE `live` SET name = ?, description = ? WHERE id = ?;");
    $stmt->bind_param('sss', $title, $desc, $live["id"]);
    $stmt->execute();
    $stmt->close();
    $mysqli->close();
    $live = getLive($live["id"]);
  } elseif ($_POST["type"] == "prop_vote_start") {
    if (empty(loadVote($live["id"]))) {
      createVote($live["id"], s($_POST["vote_title"]), [
        s($_POST["vote1"]), s($_POST["vote2"]), s($_POST["vote3"]), s($_POST["vote4"])
      ], liveTag($live));
    }
  } elseif ($_POST["type"] == "prop_vote_end") {
    endVote($live["id"], liveTag($live));
  }
}

$liveurl = liveUrl($live["id"]);
$comurl = (empty($_SERVER["HTTPS"]) ? "http://" : "https://") . $_SERVER["HTTP_HOST"] . u("api/client/comment_viewer") . "?id=" . $live["id"];

$share_normal = "#KnzkLive で配信中！\n{$live["name"]}\n{$liveurl}\n\nコメントタグ: #".liveTag($live);
$share_knzk = "{$liveurl}\n{$liveurl}\n{$liveurl}";

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
  <a href="" class="btn btn-info btn-sm">再読込</a>
</div>
<?php if ($live["is_started"] == "0") : ?>
  <div class="container">
    <div class="box">
      <div class="alert alert-info" role="alert">
        <form method="post">
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
  <div class="container">
    <div class="box">
      <b>神崎と愉快な小道具たち</b>
      <div class="row">
        <div class="col-md-6">
          <div class="alert alert-secondary" role="alert">
            <form method="post">
              <input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']?>">
              <b>投票</b>
              <?php if (empty($vote)) : ?>
                <input type="hidden" name="type" value="prop_vote_start">
              <div class="form-group">
                <input type="text" class="form-control" name="vote_title" placeholder="投票タイトル">
              </div>
              <hr>
              <?php for ($i = 1; $i < 5; $i++) : ?>
                <div class="form-group">
                  <input type="text" class="form-control" name="vote<?=$i?>" placeholder="内容<?=$i?>">
                </div>
              <?php endfor; ?>
              <small class="form-text text-muted">3と4はオプション</small>

              <button type="submit"
                      onclick="return confirm('投票を開始します。\nよろしいですか？');"
                      class="btn btn-success btn-sm btn-block">
                :: 投票を作成 ::
              </button>
              <?php else : ?>
                <input type="hidden" name="type" value="prop_vote_end">
              現在、<b><?=($vote["v1_count"] + $vote["v2_count"] + $vote["v3_count"] + $vote["v4_count"])?></b>人が投票しています
                <button type="submit"
                        onclick="return confirm('投票を終了します。\nよろしいですか？');"
                        class="btn btn-warning btn-sm btn-block">
                  :: 投票を終了 ::
                </button>
              <small>* 自動で閉じるのを実装するのが面倒だったから自分で閉じてね.</small><br>
              <?php endif; ?>
              <b>* あなたのMastodonアカウントで投票内容が投稿されます。</b>
            </form>
          </div>
        </div>
      </div>
      </div>
  </div>
  <hr>
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
            <a class="btn btn-primary" href="<?=$liveurl?>" target="_blank">Open</a>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <b>コメビュURL:</b><br>
        <div class="input-group">
          <input class="form-control" type="text" value="<?=$comurl?>" readonly>
          <div class="input-group-append">
            <a class="btn btn-secondary" href="<?=$comurl?>" target="_blank">Open</a>
          </div>
        </div>
      </div>
    </div>
  </div>
  <hr>

  <div class="box">
    <b>配信サーバー情報:</b><br>
    <span class="text-danger">* この情報は漏洩すると第三者に配信を乗っ取られる可能性がありますので十分にご注意ください。</span><br>
    <div class="row">
      <div class="col-md-6">
        <div class="input-group">
          <div class="input-group-prepend">
            <span class="input-group-text" id="url">URL</span>
          </div>
          <input type="text" class="form-control" aria-describedby="url" readonly value="rtmp://<?=$slot["server_ip"]?>/live">
        </div>
      </div>
      <div class="col-md-6">
        <div class="input-group">
          <div class="input-group-prepend">
            <span class="input-group-text" id="key">ストリームキー</span>
          </div>
          <input type="text" class="form-control" aria-describedby="key" readonly placeholder="クリックで表示" onclick="window.prompt('ストリームキー', '<?=$live["id"]?>stream?token=<?=$live["token"]?>')">
        </div>
      </div>
    </div>
  </div>
  <hr>

  <div class="box">
    <div class="row">
      <div class="col-md-6">
        <b>配信をシェア(配信者用):</b><br>
        <div class="btn-group" role="group">
          <a href="https://<?=$env["masto_login"]["domain"]?>/share?text=<?=urlencode($share_normal)?>" target="_blank" class="btn btn-primary">標準</a>
          <a href="https://<?=$env["masto_login"]["domain"]?>/share?text=<?=urlencode($share_knzk)?>" target="_blank" class="btn btn-primary">神崎</a>
        </div>
      </div>
      <div class="col-md-6">
        <?php if ($live["is_live"] === 2) : ?>
          <b>配信を終了:</b><br>
          <span class="text-danger">* ソフト側(OBSなど)で配信終了するとボタンが使用できます。</span><br>
        <?php elseif ($live["is_live"] === 1) : ?>
          <a href="<?=u("live_manage")?>?mode=shutdown&t=<?=$_SESSION['csrf_token']?>" onclick="return confirm('配信を終了して、配信枠を返却します。\nよろしいですか？');" class="btn btn-danger btn-lg btn-block">配信を終了</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php include "../include/footer.php"; ?>
  <script src="js/knzklive.js"></script>
</body>
</html>
