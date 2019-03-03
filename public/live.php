<?php
require_once("../lib/bootloader.php");

$id = s($_GET["id"]);
if (!$id) {
  http_response_code(421);
  exit("ERR:配信IDを入力してください。");
}

$live = getLive($id);
if (!$live) {
  http_response_code(404);
  exit("ERR:この配信は存在しません。");
}

$slot = getSlot($live["slot_id"]);
$my = getMe();
$blocking = blocking_user($live["user_id"], $_SERVER["REMOTE_ADDR"], $my ? $my["acct"] : null);
if ((!$my && $live["privacy_mode"] == "3") || !empty($blocking["is_blocking_watch"])) {
  http_response_code(403);
  exit("ERR:この配信は非公開です。| " . ($my ? "" : "<a href='".u("login")."'>ログイン</a>"));
}

if ($my["id"] != $live["user_id"] && $live["is_started"] == "0") {
  http_response_code(403);
  exit("ERR:この配信はまだ開始されていません。 | " . ($my ? "" : "<a href='".u("login")."'>ログイン</a>"));
}

if (isset($_POST["sensitive"])) $_SESSION["sensitive_allow"] = true;

$liveUser = getUser($live["user_id"]);

$new_live = ($liveUser["live_current_id"] !== 0 && $liveUser["live_current_id"] !== $live["id"]) ? getLive($liveUser["live_current_id"]) : null;
if (!empty($new_live) && ($new_live["privacy_mode"] !== 1 || $new_live["is_started"] !== 1)) $new_live = null;

$liveurl = liveUrl($live["id"]);

$vote = loadVote($live["id"]);
?>
<!doctype html>
<html lang="ja" data-page="live">
<head>
  <?php include "../include/header.php"; ?>
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.4.1/css/all.css" integrity="sha384-5sAR7xN1Nv6T6+dT2mhtzEpVJvfS3NScPQTrOxhwjIuvcA67KV2R5Jz6kr4abQsz" crossorigin="anonymous">
  <title id="title-name"><?=$live["name"]?> - <?=$env["Title"]?></title>

  <meta property="og:title" content="<?=$live["name"]?>"/>
  <meta property="og:type" content="website"/>
  <meta content="summary" property="twitter:card" />
  <meta property="og:url" content="<?=$liveurl?>"/>
  <meta property="og:image" content="<?=$liveUser["misc"]["avatar"]?>"/>
  <meta property="og:site_name" content="<?=$env["Title"]?>"/>
  <meta property="og:description" content="<?=s($live["description"])?>"/>
  <meta name="description" content="<?=s($live["description"])?> by <?=s($liveUser["name"])?>">

  <script>
    window.config.live = {
      id: <?=$live["id"]?>,
      hashtag_o: "<?=liveTag($live)?>",
      hashtag: " #<?=liveTag($live)?>" + (config.account && config.account.domain === "twitter.com" ? " - <?=$liveurl?>" : ""),
      url: "<?=$liveurl?>",
      is_broadcaster: <?=$my && $live["user_id"] === $my["id"] ? "true" : "false"?>,
      created_at: "<?=dateHelper($live["created_at"])?>",
      websocket_url: "<?=($env["is_testing"] ? "ws://localhost:3000/api/streaming" : "wss://" . $env["domain"] . $env["RootUrl"] . "api/streaming")?>/live/<?=s($live["id"])?>",
      account: {
        id: <?=$liveUser["id"]?>,
        acct: "<?=$liveUser["acct"]?>",
        name: "<?=$liveUser["name"]?>"
      },
      watch_data: {},
      websocket: {},
      heartbeat: {},
      page: "livepage"
    }

    window.onload = function() {
      window.live = knzk.live;
      live.ready();
<?php if (!$live["misc"]["able_comment"]) : ?>
      $(".comment_block").hide();
<?php endif; ?>
    }
  </script>
</head>
<body>
<?php $navmode = "fluid"; include "../include/navbar.php"; ?>
<?php if (!empty($new_live)) : ?>
  <div class="container">
    <div class="alert alert-info" role="alert">
      <h4>この配信者は現在配信中です！</h4>
      <a href="<?=liveUrl($new_live["id"])?>"><b><?=$new_live["name"]?></b></a> <small>(<?=date("Y/m/d H:i", strtotime($new_live["created_at"]))?> に開始)</small>
    </div>
  </div>
<?php endif; ?>
<?php if ($live["misc"]["is_sensitive"] && !isset($_SESSION["sensitive_allow"])) : ?>
<div class="container">
  <h1>警告！</h1>
  この配信はセンシティブな内容を含む配信の可能性があります。本当に視聴しますか？
  <p>
    「<b><?=$live["name"]?></b>」 by <?=$liveUser["name"]?>
  </p>
  <form method="post">
    <input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']?>">
    <input type="hidden" name="sensitive" value="1">
    <button type="submit" class="btn btn-danger btn-lg btn-block">:: 視聴する ::</button>
  </form>
</div>
<?php else : ?>
<div class="container-fluid">
  <div class="row">
    <div class="col-md-9">
      <div id="err_live" class="text-warning"></div>
      <div id="is_not_started" class="invisible">* この配信はまだ開始されていません。現在はあなたのみ視聴できます。<a href="<?=u("live_manage")?>">配信開始はこちらから</a></div>
      <?php if ($my["id"] === $live["user_id"]) : ?>
        <div class="text-warning">* これは自分の放送です。ハウリング防止の為自動でミュートしています。</div>
      <?php endif; ?>
      <div class="embed-responsive embed-responsive-16by9" id="live">
        <iframe class="embed-responsive-item" src="<?=u("live_embed")?>?id=<?=$id?>&rtmp=<?=$slot["server"]?>" allowfullscreen id="iframe" allow="autoplay; fullscreen"></iframe>
      </div>
      <span class="float-right">
        <span class="live-info" id="time"></span>
        <span class="live-info"><i class="fas fa-hat-wizard"></i> <b class="point_count"><?=$live["point_count"]?></b>KP</span>
        <span class="live-info"><i class="fas fa-comments"></i> <b id="comment_count"><?=s($live["comment_count"])?></b></span>

        <span id="count_open">
          <i class="fas fa-users"></i> <b class="count"><?=$live["viewers_count"]?></b> / <span class="max"><?=$live["viewers_max"]?></span>
        </span>
        <span id="count_end" class="invisible">
          総視聴者数: <span class="max"><?=$live["viewers_max"]?></span>人    最大同時視聴者数: <span id="max_c"><?=$live["viewers_max_concurrent"]?></span>人
        </span>
      </span>
      <br>
      <div class="float-right">
        <?php if ($live["is_live"] !== 0 && $my["id"] === $live["user_id"]) : ?>
          <button type="button" class="btn btn-outline-warning live_edit invisible" onclick="live.admin.undoEditLive()"><i class="fas fa-times"></i> 編集廃棄</button>
          <button type="button" class="btn btn-outline-success live_edit invisible" onclick="live.admin.editLive()" style="margin-right:10px"><i class="fas fa-check"></i> 編集完了</button>
        <?php endif; ?>
        <?php if (!empty($my) && $live["is_live"] !== 0) : ?>
          <button type="button" class="btn btn-outline-success" data-toggle="modal" data-target="#itemModal"><i class="fas fa-hat-wizard"></i> アイテム</button>
        <?php endif; ?>

        <?php if (donation_url($liveUser["id"], false) && $live["is_live"] !== 0) : ?>
          <button type="button" class="btn btn-outline-warning" data-toggle="modal" data-target="#chModal"><i class="fas fa-donate"></i> 支援 (CH)</button>
        <?php elseif (donation_url($liveUser["id"])) : ?>
          <a class="btn btn-outline-warning" href="<?=donation_url($liveUser["id"])?>" target="_blank"><i class="fas fa-donate"></i> 支援</a>
        <?php endif; ?>

        <button type="button" class="btn btn-link side-buttons" onclick="live.share.share()"><i class="fas fa-share-square"></i> 共有</button>
      </div>
      <p></p>
      <h4 id="live-name" class="live_info"><?=$live["name"]?></h4>

      <div class="input-group col-md-6 invisible live_edit" style="margin-bottom:20px">
        <div class="input-group-prepend">
          <span class="input-group-text" id="edit_title_label">タイトル</span>
        </div>
        <input type="text" class="form-control" placeholder="タイトル (100文字以下)" value="<?=$live["name"]?>" id="edit_name">
      </div>
      <span class="text-secondary">
        <?php if ($live["is_live"] !== 0) : ?>
          <?=date("Y/m/d H:i", strtotime($live["created_at"]))?> に開始
        <?php else : ?>
          <?=date("Y/m/d H:i", strtotime($live["created_at"]))?> - <?=date("Y/m/d H:i", strtotime($live["ended_at"]))?>
        <?php endif; ?>
      </span>
      <p id="live-description" class="live_info"><?=HTMLHelper($live["description"])?></p>

      <div class="input-group col-md-8 invisible live_edit">
        <div class="input-group-prepend">
          <span class="input-group-text">説明</span>
        </div>
        <textarea class="form-control" id="edit_desc" rows="4"><?=$live["description"]?></textarea>
      </div>

      <?php if ($live["is_live"] !== 0 && $my["id"] === $live["user_id"]) : ?>
      <hr>
      <?php include "../include/live/adminpanel.php"; ?>
      <?php endif; ?>
      <p>
        <a href="<?=u("report")?>?liveid=<?=$live["id"]?>" target="_blank" class="text-danger">配信を通報する</a>
      </p>
    </div>
    <div class="col-md-3" id="comment">
      <?php include "../include/live/comment.php"; ?>
    </div>
  </div>
</div>

<?php include "../include/live/modals.php"; ?>
<?php if ($my["id"] === $live["user_id"]) include "../include/live/add_blocking.php"; ?>
<script id="com_tmpl" type="text/x-handlebars-template">
  <div id="post_{{id}}" class="comment card mb-2">
    <div class="content card-body">
      <div class="float-left">
        <img src="{{account.avatar}}" class="avatar rounded" width="50" height="50" onclick="live.live.userDropdown(this, '{{id}}', '{{account.acct}}', '{{account.url}}')"/>
      </div>
      <div class="float-right card-text">
        <span onclick="live.live.userDropdown(this, '{{id}}', '{{account.acct}}', '{{account.url}}')" class="name text-truncate">
          {{#if donator_color}}
          <span class="badge badge-pill" style="background:{{donator_color}}">
          {{/if}}
          <b>{{account.display_name}}</b>
          {{#if donator_color}}
          </span>
          {{/if}}
        </span>
        <div class="postcontent card-text">
          {{{content}}}
        </div>
      </div>
    </div>
  </div>
</script>
<?php include "../include/footer.php"; ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/handlebars.js/4.0.12/handlebars.min.js" integrity="sha256-qlku5J3WO/ehJpgXYoJWC2px3+bZquKChi4oIWrAKoI=" crossorigin="anonymous"></script>
<?php endif; // sensitive ?>
</body>
</html>
