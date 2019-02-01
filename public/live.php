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
if (!$my && $live["privacy_mode"] == "3") {
  http_response_code(403);
  exit("ERR:この配信は非公開です。| " . ($my ? "" : "<a href='".u("login")."'>ログイン</a>"));
}

if ($my["id"] != $live["user_id"] && $live["is_started"] == "0") {
  http_response_code(403);
  exit("ERR:この配信はまだ開始されていません。 | " . ($my ? "" : "<a href='".u("login")."'>ログイン</a>"));
}

if (isset($_POST["sensitive"])) $_SESSION["sensitive_allow"] = true;

$liveUser = getUser($live["user_id"]);

$liveurl = liveUrl($live["id"]);

$vote = loadVote($live["id"]);
?>
<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css" integrity="sha384-GJzZqFGwb1QTTN6wy59ffF1BuGJpLSa9DkKMp0DgiMDm4iYMj70gZWKYbI706tWS" crossorigin="anonymous">
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.4.1/css/all.css" integrity="sha384-5sAR7xN1Nv6T6+dT2mhtzEpVJvfS3NScPQTrOxhwjIuvcA67KV2R5Jz6kr4abQsz" crossorigin="anonymous">
  <link rel="stylesheet" href="style.css">
  <link rel="shortcut icon" type="image/x-icon" href="<?=$env["RootUrl"]?>favicon.ico">
  <title id="title-name"><?=$live["name"]?> - <?=$env["Title"]?></title>

  <meta name="description" content="<?=s($live["description"])?> by <?=s($liveUser["name"])?>">

  <meta property="og:title" content="<?=$live["name"]?>"/>
  <meta property="og:type" content="website"/>
  <meta content="summary" property="twitter:card" />
  <meta property="og:url" content="<?=$liveurl?>"/>
  <meta property="og:image" content="<?=$liveUser["misc"]["avatar"]?>"/>
  <meta property="og:site_name" content="<?=$env["Title"]?>"/>
  <meta property="og:description" content="<?=s($live["description"])?>"/>

  <meta name="application-name" content="<?=$env["Title"]?>">
  <meta name="msapplication-TileColor" content="#000000">
  <meta name="theme-color" content="#000000">

  <style>
    #comments {
      overflow-y: scroll;
      overflow-x: hidden;
      height: 600px;
    }
    #comments::-webkit-scrollbar {
      width: 10px;
    }
    #comments::-webkit-scrollbar-thumb {
      background-color: #343a40;
      border-radius: 5px;
    }
    .hashtag {
      display: none;
    }
    .avatar_img_navbar {
      float: left;
      margin-right: 10px;
    }
    .side-buttons, .side-buttons:hover {
      color: #17a2b8;
      text-decoration: none;
    }
    .modal-title {
      color: #212529;
    }
    .share_buttons button {
      margin: 3px;
      padding: .375rem .1rem;
    }
    #live-name {
      font-weight: 600;
    }

    .is_wide {
      overflow: hidden;
    }

    .is_wide .nav-container {
      display: none;
    }

    .is_wide #iframe {
      width: 100%;
      position: fixed;
    }

    .is_wide #comment {
      position: fixed;
      right: 0;
      height: calc(100% - 35px);
      background: rgba(0,0,0,.3);
    }

    .is_wide footer {
      display: none;
    }

    .is_wide #comments {
      height: calc(100% - 250px);
    }

    .live_tools {
      color: #000;
    }

    .comment {
      display: flex;
      margin-bottom: 10px;
      word-wrap: break-word;
      word-break: break-all;
    }

    .comment .avatar {
      width: 50px;
      height: 50px;
      background-color: #fff;
      cursor: pointer;
    }

    .comment .content {
      padding-left: 5px;
    }

    .comment .name {
      cursor: pointer;
    }
  </style>
</head>
<body>
<?php $navmode = "fluid"; include "../include/navbar.php"; ?>
<?php if ($live["is_sensitive"] == 1 && !isset($_SESSION["sensitive_allow"])) : ?>
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
      <span style="float: right">
          <span id="h"></span><span id="m"></span><span id="s"></span>
          <span id="count_open">
            アイテム: <b class="point_count"><?=$live["point_count"]?></b>KP · 視聴者数: <b id="count"><?=$live["viewers_count"]?></b> / <span class="max"><?=$live["viewers_max"]?></span>
          </span>
          <span id="count_end" class="invisible">
            アイテム: <b class="point_count"><?=$live["point_count"]?></b>KP · 総視聴者数: <span class="max"><?=$live["viewers_max"]?></span>人 · 最大同時視聴者数: <span id="max_c"><?=$live["viewers_max_concurrent"]?></span>人
          </span>
        </span>
      <br>
      <div style="float: right">
        <?php if ($live["is_live"] !== 0 && $my["id"] === $live["user_id"]) : ?>
          <button type="button" class="btn btn-outline-warning live_edit invisible" onclick="undo_edit_live()"><i class="fas fa-times"></i> 編集廃棄</button>
          <button type="button" class="btn btn-outline-success live_edit invisible" onclick="edit_live()" style="margin-right:10px"><i class="fas fa-check"></i> 編集完了</button>
        <?php endif; ?>
        <?php if (!empty($my) && $live["is_live"] !== 0) : ?>
          <button type="button" class="btn btn-outline-success" data-toggle="modal" data-target="#itemModal"><i class="fas fa-hat-wizard"></i> アイテム</button>
        <?php endif; ?>
        <?php if (!empty($liveUser["misc"]["donate_url"])) : ?>
          <a class="btn btn-outline-warning" href="<?=s($liveUser["misc"]["donate_url"])?>" target="_blank"><i class="fas fa-donate"></i> 支援</a>
        <?php endif; ?>
        <button type="button" class="btn btn-link side-buttons" onclick="share()"><i class="fas fa-share-square"></i> 共有</button>
      </div>
      <p></p>
      <h4 id="live-name" class="live_info"><?=$live["name"]?></h4>

      <div class="input-group col-md-6 invisible live_edit" style="margin-bottom:20px">
        <div class="input-group-prepend">
          <span class="input-group-text" id="edit_title_label">タイトル</span>
        </div>
        <input type="text" class="form-control" placeholder="タイトル (100文字以下)" value="<?=$live["name"]?>" id="edit_name">
      </div>

      <p>
        <img src="<?=$liveUser["misc"]["avatar"]?>" class="avatar_img_navbar rounded-circle"/>
        <b><?=$liveUser["name"]?></b>
      </p>
      <span class="text-secondary"><?=date("Y/m/d", strtotime($live["created_at"]))?>に開始</span>
      <p id="live-description" class="live_info"><?=HTMLHelper($live["description"])?></p>

      <div class="input-group col-md-8 invisible live_edit">
        <div class="input-group-prepend">
          <span class="input-group-text">説明</span>
        </div>
        <textarea class="form-control" id="edit_desc" rows="4"><?=$live["description"]?></textarea>
      </div>

      <?php if ($live["is_live"] !== 0 && $my["id"] === $live["user_id"]) : ?>
      <hr>
      <div class="card live_tools">
        <div class="card-header">
          配信管理
        </div>
        <div class="card-body">
          <h5>基本設定</h5>
          <button type="button" class="btn btn-danger" onclick="stop_broadcast()"><i class="far fa-stop-circle"></i> 配信終了</button>
          <button type="button" class="btn btn-primary" onclick="openEditLive()"><i class="fas fa-pencil-alt"></i> 編集</button>
          <!--
          <button type="button" class="btn btn-warning" onclick="liveSetting()"><i class="fas fa-eye-slash"></i> センシティブを有効化</button>
          <button type="button" class="btn btn-warning" onclick="liveSetting()"><i class="fas fa-hat-wizard"></i> アイテムを無効化</button>
          -->
          <hr>
          <!--
          <h5>コメント管理</h5>
          <button type="button" class="btn btn-warning" onclick="openEditLive()"><i class="fas fa-comment-slash"></i> コメントを無効化</button>
          <button type="button" class="btn btn-info" onclick="openEditLive()"><i class="fas fa-comment-slash"></i> NGワード管理</button>
          <button type="button" class="btn btn-info" onclick="openEditLive()"><i class="fas fa-user-slash"></i> ブロックユーザ管理</button>
          <button type="button" class="btn btn-info" onclick="openEditLive()"><i class="fas fa-user-shield"></i> モデレータ管理</button>
          <hr>
          -->
          <h5>ツール</h5>
          <button type="button" class="btn btn-success" data-toggle="modal" data-target="#enqueteModal" id="open_enquete_btn"><i class="fas fa-poll-h"></i> アンケート</button>
          <button type="button" class="btn btn-warning" onclick="closeEnquete()" id="close_enquete_btn" style="display: none"><i class="fas fa-poll-h"></i> アンケートを終了</button>
          <!--
          <hr>
          <h5>ログ</h5>
          <button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#modlogModal"><i class="fas fa-gavel"></i> モデレーションログ</button>
          <button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#usersModal"><i class="fas fa-users"></i> リスナー一覧</button>
          -->
        </div>
      </div>
      <?php endif; ?>
    </div>
    <div class="col-md-3" id="comment">
      <div>
        <?php if (!empty($my)) : ?>
          <div class="<?=(empty($vote) || !empty($_SESSION["prop_vote_" . $live["id"]]) ? "invisible" : "")?>" id="prop_vote">
            <div class="alert alert-info mt-3">
              <h5><i class="fas fa-poll-h"></i> <b id="vote_title"><?=(empty($vote) ? "タイトル" : $vote["title"])?></b></h5>
              <button type="button" class="btn btn-info btn-block btn-sm mt-1" id="vote1" onclick="vote(1)"><?=(empty($vote) ? "投票1" : $vote["v1"])?></button>
              <button type="button" class="btn btn-info btn-block btn-sm mt-1" id="vote2" onclick="vote(2)"><?=(empty($vote) ? "投票2" : $vote["v2"])?></button>
              <button type="button" class="btn btn-info btn-block btn-sm mt-1 <?=(empty($vote) || empty($vote["v3"]) ? "invisible" : "")?>" id="vote3" onclick="vote(3)"><?=(empty($vote) ? "投票3" : $vote["v3"])?></button>
              <button type="button" class="btn btn-info btn-block btn-sm mt-1 <?=(empty($vote) || empty($vote["v4"]) ? "invisible" : "")?>" id="vote4" onclick="vote(4)"><?=(empty($vote) ? "投票4" : $vote["v4"])?></button>
            </div>
            <hr>
          </div>
        <?php endif; ?>
        <div class="mt-2 mb-2">
          #<?=liveTag($live)?>: <b id="comment_count"><?=s($live["comment_count"])?></b>コメ
        </div>
        <?php if ($my) : ?>
          <div class="form-group">
            <textarea class="form-control" id="toot" rows="3" placeholder="コメント... (<?=$my["acct"]?>としてトゥート/コメント)" onkeyup="check_limit()"></textarea>
          </div>


          <div class="custom-control custom-checkbox float-left">
            <input type="checkbox" class="custom-control-input" id="no_toot" value="1" <?=($my["misc"]["no_toot_default"] ? "checked" : "")?>>
            <label class="custom-control-label" for="no_toot">
              <small>コメントのみ投稿 <a href="#" onclick="alert('有効にした状態で投稿すると、KnzkLiveにコメントしますが<?=$_SESSION["account_provider"]?>には投稿されません。');return false">？</a></small>
            </label>
          </div>
          <div style="text-align: right">
            <b id="limit"></b>  <button class="btn btn-outline-primary" onclick="post_comment()">コメント</button>
          </div>

        <?php else : ?>
          <p>
            <span class="text-warning">* コメントを投稿するにはログインしてください。<?=(!$liveUser["misc"]["live_toot"] ? "<br><br>{$env["masto_login"]["domain"]}のアカウントにフォローされているアカウントから #".liveTag($live)." をつけてトゥートしてもコメントする事ができます。" : "")?></span>
          </p>
        <?php endif; ?>
        <p class="invisible" id="err_comment">
          * コメントの読み込み中にエラーが発生しました。 <a href="javascript:loadComment()">再読込</a>
        </p>
        <hr>
      </div>
      <div id="comments"></div>
    </div>
  </div>
</div>
<?php endif; ?>
<div class="modal fade" id="shareModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">共有</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="row justify-content-md-center share_buttons">
          <button class="btn btn-outline-info col-md-2" onclick="share_modal('twitter')">
            <i class="fab fa-twitter fa-fw fa-2x"></i><br>
            Twitter
          </button>
          <button class="btn btn-outline-primary col-md-2" onclick="share_modal('mastodon')">
            <i class="fab fa-mastodon fa-fw fa-2x"></i><br>
            Mastodon
          </button>
          <button class="btn btn-outline-danger col-md-2" onclick="share_modal('weibo')">
            <i class="fab fa-weibo fa-fw fa-2x"></i><br>
            Weibo
          </button>
          <button class="btn btn-outline-primary col-md-2" onclick="share_modal('facebook')">
            <i class="fab fa-facebook fa-fw fa-2x"></i><br>
            Facebook
          </button>
          <button class="btn btn-outline-success col-md-2" onclick="share_modal('line')">
            <i class="fab fa-line fa-fw fa-2x"></i><br>
            LINE
          </button>
          <button class="btn btn-outline-info col-md-2" onclick="share_modal('skype')">
            <i class="fab fa-skype fa-fw fa-2x"></i><br>
            Skype
          </button>
          <button class="btn btn-outline-danger col-md-2" onclick="share_modal('flipboard')">
            <i class="fab fa-flipboard fa-fw fa-2x"></i><br>
            Flipboard
          </button>
        </div>
        <div class="row" style="margin-top: 10px">
          <div class="col-md-12">
            <div class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-text" id="share_url">URL</span>
              </div>
              <input type="text" class="form-control" aria-describedby="share_url" readonly value="<?=$liveurl?>" onclick="this.select(0,this.value.length)">
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="itemModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-hat-wizard"></i> アイテム <span class="badge badge-info"><b class="now_user_point"><?=$my["point_count"]?></b>KP</span></h5>

        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <h5>絵文字</h5>
        <div class="row">
          <div class="col-sm-4">
            絵文字:
            <select class="form-control" id="item_emoji_emoji">
              <option>👍</option>
              <option>❤️</option>
              <option>👏️</option>
              <option>🎉️</option>
              <option value="liver">配信者のアイコン</option>
              <option value="me">あなたのアイコン</option>
            </select>
          </div>
          <div class="col-sm-4">
            方向:
            <select class="form-control" id="item_emoji_dir">
              <option value="left-to-right">左から右</option>
              <option value="right-to-left">右から左</option>
              <option value="top-to-bottom">上から下</option>
              <option value="bottom-to-top">下から上</option>
            </select>
          </div>
          <div class="col-sm-4">
            個数 <small>(1~100, <b>n*5</b>KP)</small>:
            <input type="number" class="form-control" id="item_emoji_count" value="1" min="1" max="100" onkeyup="update_money_disp('emoji')" onchange="update_money_disp('emoji')">
          </div>
        </div>
        <div class="mt-2">
          <div class="custom-control custom-checkbox float-left">
            <input type="checkbox" class="custom-control-input" id="item_emoji_spin" onchange="update_money_disp('emoji')">
            <label class="custom-control-label" for="item_emoji_spin">
              回転あり (+<b>50</b>KP)<br>
              <small>一部端末で表示されない可能性があります</small>
            </label>
          </div>
          <div class="text-right">
            <button class="btn btn-success" onclick="item_buy('emoji')"><span id="item_emoji_point">5</span>KPで投下</button>
          </div>
        </div>
        <hr>
        <?php if ($liveUser["id"] === 2 || $env["is_testing"]) : ?>
        <h5>神崎コンギョ (音)</h5>
        コ　ン　ギ　ョ
        <div class="text-right">
          <button class="btn btn-success" onclick="item_buy('knzk_kongyo')">1000KPで投下</button>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="enqueteModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">アンケートを新規作成</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <input type="text" class="form-control" id="open_vote_title" placeholder="投票タイトル">
        </div>
        <hr>
        <?php for ($i = 1; $i < 5; $i++) : ?>
          <div class="form-group">
            <input type="text" class="form-control" id="open_vote<?=$i?>" placeholder="内容<?=$i?>">
          </div>
        <?php endfor; ?>

        <div class="form-group">
          <div class="custom-control custom-checkbox">
            <input type="checkbox" class="custom-control-input" id="vote_ispost" value="1"  <?=($my["misc"]["no_toot_default"] ? "checked" : "")?>>
            <label class="custom-control-label" for="vote_ispost">
              Mastodonに投票内容を投稿しない
            </label>
          </div>
        </div>

        <small class="form-text text-muted">3と4はオプション</small>

        <button type="submit"
                onclick="open_enquete()"
                class="btn btn-success btn-block">
          :: 投票を作成 ::
        </button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="sensitiveModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">警告！</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        この先、配信はセンシティブな内容を含む可能性があります。続行しますか？
        <button type="button" class="btn btn-danger btn-lg btn-block" data-dismiss="modal" onclick="document.getElementById('iframe').src = frame_url">:: 視聴する ::</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="listModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">あああ</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <ul class="list-group">
          <li class="list-group-item">Cras justo odio</li>
          <li class="list-group-item">Dapibus ac facilisis in</li>
          <li class="list-group-item">Morbi leo risus</li>
          <li class="list-group-item">Porta ac consectetur ac</li>
          <li class="list-group-item">Vestibulum at eros</li>
        </ul>
      </div>
    </div>
  </div>
</div>

<script id="com_tmpl" type="text/x-handlebars-template">
  <div id="post_{{id}}" class="comment">
    <div>
      <img src="{{account.avatar}}" class="avatar rounded-circle" width="50" height="50" onclick="userDropdown('{{account.acct}}', '{{account.url}}')"/>
    </div>
    <div class="content">
      <span onclick="userDropdown('{{account.acct}}', '{{account.url}}')" class="name"><b>{{account.display_name}}</b> <small>@{{account.acct}}</small></span>
      {{{content}}}
    </div>
  </div>
</script>
<?php include "../include/footer.php"; ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/handlebars.js/4.0.12/handlebars.min.js" integrity="sha256-qlku5J3WO/ehJpgXYoJWC2px3+bZquKChi4oIWrAKoI=" crossorigin="anonymous"></script>
<script src="js/knzklive.js?2018-12-13"></script>
<script>
  const inst = "<?=$env["masto_login"]["domain"]?>";
  let login_inst = "<?=isset($_SESSION["login_domain"]) ? s($_SESSION["login_domain"]) : ""?>";
  if (!login_inst) login_inst = inst;
  const hashtag_o = "<?=liveTag($live)?>";
  const hashtag = " #" + hashtag_o + (login_inst === "twitter.com" ? " via <?=$liveurl?>" : "");
  const token = "<?=$my && $_SESSION["token"] ? s($_SESSION["token"]) : ""?>";
  var heartbeat, cm_ws, watch_data = {}, io, w_heartbeat;
  var api_header = {'content-type': 'application/json'};
  if (token) api_header["Authorization"] = 'Bearer ' + token;
  var frame_url = "";

  function watch(first) {
    fetch('<?=u("api/client/watch")?>?id=<?=s($live["id"])?>', {
      method: 'GET',
      credentials: 'include',
    }).then(function(response) {
      if (response.ok) {
        return response.json();
      } else {
        throw response;
      }
    }).then(function(json) {
      const err = elemId("err_live");
      err.innerHTML = "";

      if (json["live_status"] === 1) err.innerHTML = "配信者からデータが送信されていません。";
      if (json["live_status"] === 0) {
        err.innerHTML = "この配信は終了しました。";
        widemode("hide");
        elemId("count_open").className = "invisible";
        elemId("count_end").className = "";
        if (watch_data["live_status"] !== 0)
          document.getElementById('iframe').contentWindow.end();
      }
      if (json["live_status"] === 2 && watch_data["live_status"] !== 2) reloadLive();

      elemId("is_not_started").className = json["is_started"] ? "invisible" : "text-warning";

      if (json["name"] !== watch_data["name"]) {
        elemId("live-name").innerHTML = json["name"];
        elemId("title-name").innerHTML = json["name"] + ` - <?=$env["Title"]?>`;
      }
      if (json["description"] !== watch_data["description"]) elemId("live-description").innerHTML = json["description"];

      if (json["viewers_count"] !== watch_data["viewers_count"]) elemId("count").innerHTML = json["viewers_count"];
      if (json["point_count"] !== watch_data["point_count"]) $(".point_count").html(json["point_count"]);
      if (json["viewers_max"] !== watch_data["viewers_max"]) $(".max").html(json["viewers_max"]);
      if (json["viewers_max_concurrent"] !== watch_data["viewers_max_concurrent"]) elemId("max_c").innerHTML = json["viewers_max_concurrent"];
      watch_data = json;
      if (first) setInterval(date_disp, 1000);
    }).catch(function(error) {
      console.error(error);
      elemId("err_live").innerHTML = "データが読み込めません: ネットワークかサーバに問題が発生しています...";
    });
  }

  function update_watch() {
    fetch('<?=u("api/client/update_watching")?>?id=<?=s($live["id"])?>', {
      method: 'GET',
      credentials: 'include',
    });
  }

  function date_disp() {
    /* thx https://www.tagindex.com/javascript/time/timer2.html */
    const now = watch_data["live_status"] === 0 ? new Date(watch_data["ended_at"]) : new Date();
    const datet = parseInt((now.getTime() - (new Date("<?=$live["created_at"]?>")).getTime()) / 1000);

    var hour = parseInt(datet / 3600);
    var min = parseInt((datet / 60) % 60);
    var sec = datet % 60;

    if (hour > 0) {
      if (hour < 10) hour = "0" + hour;
      elemId("h").innerHTML = hour + ":";
    }

    if (min < 10) min = "0" + min;
    elemId("m").innerHTML = min + ":";

    if (sec < 10) sec = "0" + sec;
    elemId("s").innerHTML = sec + " · ";
  }

  function reloadLive() {
    document.getElementById('iframe').src = document.getElementById('iframe').src;
  }

  function vote(id) {
    elemId("prop_vote").className = "invisible";
    fetch('<?=u("api/client/vote/add")?>?id=<?=s($live["id"])?>&type=' + id, {
      method: 'GET',
      credentials: 'include'
    }).then(function(response) {
      if (response.ok) {
        return response.json();
      } else {
        throw response;
      }
    }).then(function(c) {

    }).catch(function(error) {
      console.error(error);
      elemId("prop_vote").className = "";
    });
  }

  function loadComment() {
    elemId("err_comment").className = "invisible";

    fetch('https://' + inst + '/api/v1/timelines/tag/' + hashtag_o, {
      headers: {'content-type': 'application/json'},
      method: 'GET'
    })
    .then(function(response) {
      if (response.ok) {
        return response.json();
      } else {
        throw response;
      }
    })
    .then(function(json) {
      let reshtml = "";
      let ws_url = 'wss://' + inst + '/api/v1/streaming/?stream=hashtag&tag=' + hashtag_o;

      cm_ws = new WebSocket(ws_url);
      cm_ws.onopen = function() {
        heartbeat = setInterval(() => cm_ws.send("ping"), 5000);
        cm_ws.onmessage = ws_onmessage;

        cm_ws.onclose = function() {
          clearInterval(heartbeat);
          loadComment();
        };
      };

      io = new WebSocket("<?=($env["is_testing"] ? "ws://localhost:3000/api/streaming" : "wss://" . $env["domain"] . $env["RootUrl"] . "api/streaming")?>/live/<?=s($live["id"])?>");
      io.onopen = function() {
        w_heartbeat = setInterval(function () {
          if (io.readyState !== 0 && io.readyState !== 1) io.close();
          io.send("ping");
        }, 5000);
      };

      io.onmessage = function (e) {
        const data = JSON.parse(e.data);
        if (data.type === "pong" || !data.payload) return;
        const msg = JSON.parse(data.payload);
        if (data.event === "prop") {
          if (msg.type === "vote_start") {
            elemId("vote_title").textContent = msg.title;
            elemId("vote1").textContent = msg.vote[0];
            elemId("vote2").textContent = msg.vote[1];
            if (msg.vote[2]) {
              elemId("vote3").textContent = msg.vote[2];
              $("#vote3").removeClass("invisible");
            } else {
              $("#vote3").addClass("invisible");
            }

            if (msg.vote[3]) {
              elemId("vote4").textContent = msg.vote[3];
              $("#vote4").removeClass("invisible");
            } else {
              $("#vote4").addClass("invisible");
            }

            elemId("prop_vote").className = "";
          } else if (msg.type === "vote_end") {
            elemId("prop_vote").className = "invisible";
            fetch('<?=u("api/client/vote/reset")?>?id=<?=s($live["id"])?>', {
              method: 'GET',
              credentials: 'include'
            });
          } else if (msg.type === "item") {
            if (msg.item_type === "knzk_kongyo") {
              const volume = localStorage.getItem('kplayer_volume');
              const audio = new Audio('http://nzws.me/files/nzws/knzk_kongyo.mp3');
              if (volume) audio.volume = volume > 20 ? 0.2 : volume * 0.01;
              else audio.volume = 0.2;

              audio.play();

              return;
            }
            document.getElementById('iframe').contentWindow.run_item(msg.item_type, msg.item, 10);
          } else if (msg.type === "mark_sensitive") {
            const frame = document.getElementById('iframe');
            frame_url = frame.src;
            frame.src = "";
            $('#sensitiveModal').modal('show');
          }
        } else if (data.event === "update") {
          ws_onmessage(msg, "update");
        }
      };

      io.onclose = function() {
        io = null;
        clearInterval(w_heartbeat);
        w_heartbeat = null;
        loadComment();
      };

      fetch('<?=u("api/client/comment_get")?>?id=<?=s($live["id"])?>', {
        method: 'GET',
        credentials: 'include'
      }).then(function(response) {
        if (response.ok) {
          return response.json();
        } else {
          throw response;
        }
      }).then(function(c) {
        if (c) {
          json = json.concat(c);
          json.sort(function(a,b) {
            return (Date.parse(a["created_at"]) < Date.parse(b["created_at"]) ? 1 : -1);
          });
        }
        if (json) {
          let i = 0;
          const tmpl = Handlebars.compile(document.getElementById("com_tmpl").innerHTML);
          while (json[i]) {
            json[i]["me"] = login_inst === inst ? undefined : false;
            reshtml += tmpl(buildCommentData(json[i], "<?=$my["acct"]?>", inst));
            i++;
          }
        }

        elemId("comments").innerHTML = reshtml;
      }).catch(function(error) {
        console.error(error);
        elemId("err_comment").className = "text-danger";
      });
    })
    .catch(error => {
      console.log(error);
      elemId("err_comment").className = "text-danger";
    });
  }

  function post_comment() {
    const v = elemId("toot").value;
    if (!v) return;
    const isKnzk = elemId("no_toot").checked;

    const option = (isKnzk || login_inst === "twitter.com") ? {headers: {'content-type': 'application/x-www-form-urlencoded'},
      method: 'POST',
      credentials: 'include',
      body: buildQuery({
        live_id: <?=s($live["id"])?>,
        content: v,
        csrf_token: `<?=$_SESSION['csrf_token']?>`,
        is_local: isKnzk ? 1 : 0,
        content_tw: v + hashtag
      })} : {headers: api_header,
      method: 'POST',
      body: JSON.stringify({
        status: v + hashtag,
        visibility: "public"
      })};


    fetch((isKnzk || login_inst === "twitter.com") ? '<?=u("api/client/comment_post")?>' : 'https://' + login_inst + '/api/v1/statuses', option)
    .then(function(response) {
      if (response.ok) {
        return response.json();
      } else {
        throw response;
      }
    })
    .then(function(json) {
      if (json) {
        elemId("toot").value = "";
        check_limit();
      }
    })
    .catch(error => {
      console.log(error);
      alert("投稿中にエラーが発生しました。通信状況やインスタンスの状況をご確認ください。");
    });
  }

  function delete_comment(_id) {
    fetch('https://' + login_inst + '/api/v1/statuses/' + _id, {
      headers: api_header,
      method: 'DELETE'
    })
    .then(function(response) {
      if (response.ok) {
        return response.json();
      } else {
        throw response;
      }
    })
    .then(function(json) {
    })
    .catch(error => {
      console.log(error);
      alert("投稿中にエラーが発生しました。通信状況やインスタンスの状況をご確認ください。");
    });
  }

  function ws_onmessage(message, mode = "") {
    let ws_resdata, ws_reshtml;
    if (mode) { //KnzkLive Comment
      ws_resdata = {};
      ws_resdata.event = mode;
      ws_reshtml = message;
    } else { //Mastodon
      ws_resdata = JSON.parse(message.data);
      ws_reshtml = JSON.parse(ws_resdata.payload);
    }

    if (ws_resdata.event === 'update') {
      if (ws_reshtml['id']) {
        ws_reshtml["me"] = login_inst === inst ? undefined : false;
        elemId("comment_count").textContent = parseInt(elemId("comment_count").textContent) + 1;
        const tmpl = Handlebars.compile(document.getElementById("com_tmpl").innerHTML);
        elemId("comments").innerHTML = tmpl(buildCommentData(ws_reshtml, "<?=$my["acct"]?>", inst)) + elemId("comments").innerHTML;
      }
    } else if (ws_resdata.event === 'delete') {
      var del_toot = elemId('post_' + ws_resdata.payload);
      if (del_toot) del_toot.parentNode.removeChild(del_toot);
    }
  }

  function check_limit() {
    if (!token) return; //未ログイン
    const l = elemId("limit");
    const d = elemId("toot").value;
    l.innerText = (login_inst === "twitter.com" ? 140 : 500) - hashtag.length - d.length;
  }

  function share() {
    if (navigator.share) {
      navigator.share({
        title: `${watch_data["name"]} by <?=$liveUser["name"]?> - KnzkLive`,
        url: "<?=$liveurl?>"
      });
    } else {
      $('#shareModal').modal('toggle');
    }
  }

  function share_modal(mode) {
    let url = "";
    if (mode === "twitter") {
      url = `https://twitter.com/intent/tweet?url=<?=urlencode($liveurl)?>&text=` + encodeURIComponent(`${watch_data["name"]} by <?=$liveUser["name"]?> - KnzkLive`);
    } else if (mode === "mastodon") {
      const text = `【視聴中】
${watch_data["name"]} by <?=$liveUser["name"]?>

<?=$liveurl?>


#KnzkLive #<?=liveTag($live)?>`;
      url = "https://" + login_inst + "/share?text=" + encodeURIComponent(text);
    } else if (mode === "facebook") {
      url = "https://www.facebook.com/sharer/sharer.php?u=<?=urlencode($liveurl)?>";
    } else if (mode === "line") {
      url = "http://line.me/R/msg/text/?<?=urlencode($liveurl)?>";
    } else if (mode === "weibo") {
      url = `http://service.weibo.com/share/share.php?url=<?=urlencode($liveurl)?>&title=` + encodeURIComponent(`${watch_data["name"]} by <?=$liveUser["name"]?> - KnzkLive`);
    } else if (mode === "skype") {
      url = `https://web.skype.com/share?url=<?=urlencode($liveurl)?>&text=` + encodeURIComponent(`${watch_data["name"]} by <?=$liveUser["name"]?> - KnzkLive`);
    } else if (mode === "flipboard") {
      url = `https://share.flipboard.com/bookmarklet/popout?v=2&url=<?=urlencode($liveurl)?>&title=` + encodeURIComponent(`${watch_data["name"]} by <?=$liveUser["name"]?> - KnzkLive`);
    }
    window.open(url);
  }

  function widemode(mode) {
    const body = document.querySelector("body");
    body.className = ((body.className === "is_wide" && !mode) || mode === "hide") ? "" : "is_wide";
  }

  function update_money_disp(item) {
    let point = 0;
    if (item === "emoji") {
      point += parseInt(elemId("item_emoji_count").value) * 5;
      point += elemId("item_emoji_spin").checked ? 50 : 0;
    }
    elemId("item_" + item + "_point").textContent = point;
  }

  function item_buy(type, is_confirmed = false) {
    const body = {
      live_id: <?=s($live["id"])?>,
      csrf_token: `<?=$_SESSION['csrf_token']?>`,
      type: type,
      confirm: is_confirmed ? 1 : 0
    };
    if (type === "emoji") {
      body["count"] = parseInt(elemId("item_emoji_count").value);
      body["dir"] = elemId("item_emoji_dir").value;
      body["emoji"] = elemId("item_emoji_emoji").value;
      body["spin"] = elemId("item_emoji_spin").checked ? 1 : 0;
    } else if (type === "knzk_kongyo") {
    } else {
      return null;
    }

    fetch('<?=u("api/client/item_buy")?>', {
      headers: {'content-type': 'application/x-www-form-urlencoded'},
      method: 'POST',
      credentials: 'include',
      body: buildQuery(body)
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
      if (json["confirm"]) {
        if (confirm(json["point"] + "KP消費します。よろしいですか？")) {
          const p = $(".now_user_point");
          p.html(parseInt(p.html()) - json["point"]);
          item_buy(type, true);
        }
      }
      if (json["success"]) {
        $('#itemModal').modal('hide');
      }
    }).catch(function(error) {
      console.error(error);
      alert("内部エラーが発生しました");
    });
  }

  function userDropdown(acct, url) {
    let is_local = false;
    if (acct.match(/\(local\)/i)) {
      is_local = true;
      acct = acct.replace(" (local)", "");
    }
    alert("Work in Progress")
  }

  window.onload = function () {
    check_limit();
    loadComment();
    watch(true);
    setInterval(watch, 5000);
    <?php if ($live["is_live"] != 0) : ?>
    update_watch();
    setInterval(update_watch, 20000);
    <?php endif; ?>
    $('#toot').keydown(function (e){
      if (e.keyCode === 13 && (e.ctrlKey || e.metaKey)) {
        post_comment()
      }
    });
  };
</script>
<?php if ($my["id"] === $live["user_id"]) : ?>
  <script>
    function open_enquete() {
      const vote = [
        elemId("open_vote1"),
        elemId("open_vote2"),
        elemId("open_vote3"),
        elemId("open_vote4")
      ];
      const title = elemId("open_vote_title");

      if (confirm('投票を開始します。\nよろしいですか？')) {
        fetch('<?=u("api/client/live/vote")?>', {
          headers: {'content-type': 'application/x-www-form-urlencoded'},
          method: 'POST',
          credentials: 'include',
          body: buildQuery({
            csrf_token: `<?=$_SESSION['csrf_token']?>`,
            title: title.value,
            vote1: vote[0].value,
            vote2: vote[1].value,
            vote3: vote[2].value,
            vote4: vote[3].value,
            is_post: elemId("vote_ispost").checked ? 1 : 0
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
            $('#enqueteModal').modal('hide');
            $('#open_enquete_btn').hide();
            $('#close_enquete_btn').show();
            title.value = "";
            vote[0].value = "";
            vote[1].value = "";
            vote[2].value = "";
            vote[3].value = "";
          } else {
            alert("エラーが発生しました。データベースに問題が発生している可能性があります。");
          }
        }).catch(function(error) {
          console.error(error);
          alert("内部エラーが発生しました");
        });
      }
    }

    function closeEnquete() {
      if (confirm('投票を終了します。\nよろしいですか？')) {
        fetch('<?=u("api/client/live/vote")?>', {
          headers: {'content-type': 'application/x-www-form-urlencoded'},
          method: 'POST',
          credentials: 'include',
          body: buildQuery({
            csrf_token: `<?=$_SESSION['csrf_token']?>`,
            end: true
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
            $('#open_enquete_btn').show();
            $('#close_enquete_btn').hide();
          } else {
            alert("エラーが発生しました。データベースに問題が発生している可能性があります。");
          }
        }).catch(function(error) {
          console.error(error);
          alert("内部エラーが発生しました");
        });
      }
    }

    function stop_broadcast() {
      if (watch_data["live_status"] === 2) {
        alert('エラー:まだ配信ソフトウェアが切断されていません。\n(または、切断された事がまだクライアントに送信されていない可能性があります。5秒程経ってからもう一度お試しください。)');
      } else if (watch_data["live_status"] === 1) {
        if (confirm('配信を終了します。よろしいですか？')) {
          location.href = `<?=u("live_manage")?>?mode=shutdown&t=<?=$_SESSION['csrf_token']?>`;
        }
      }
    }

    function edit_live() {
      const name = elemId('edit_name').value;
      const desc = elemId('edit_desc').value;

      if (!name || !desc) {
        alert('エラー: タイトルか説明が入力されていません。');
        return;
      }

      fetch('<?=u("api/client/edit_live")?>', {
        headers: {
          'content-type': 'application/x-www-form-urlencoded',
        },
        method: 'POST',
        credentials: 'include',
        body: buildQuery({
          name: name,
          description: desc,
          csrf_token: `<?=$_SESSION['csrf_token']?>`
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
        } else {
          $('.live_info').removeClass('invisible');
          $('.live_edit').addClass('invisible');
          watch();
        }
      }).catch(function(error) {
        console.error(error);
        alert('送信中にエラーが発生しました。');
      });
    }

    function undo_edit_live() {
      elemId('edit_name').value = watch_data["name"];

      const parser = document.createElement('div');
      parser.innerHTML = watch_data["description"];
      elemId('edit_desc').value = parser.textContent;

      $('.live_info').removeClass('invisible');
      $('.live_edit').addClass('invisible');
    }

    function openEditLive() {
      $('.live_info').addClass('invisible');
      $('.live_edit').removeClass('invisible');
    }
  </script>
<?php endif; ?>
</body>
</html>
