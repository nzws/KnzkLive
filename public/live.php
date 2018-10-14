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

$liveUser = getUser($live["user_id"]);

$liveurl = liveUrl($live["id"]);
?>
<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO"
        crossorigin="anonymous">
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.4.1/css/all.css" integrity="sha384-5sAR7xN1Nv6T6+dT2mhtzEpVJvfS3NScPQTrOxhwjIuvcA67KV2R5Jz6kr4abQsz" crossorigin="anonymous">
  <link rel="stylesheet" href="style.css">
  <title id="title-name"><?=$live["name"]?> - <?=$env["Title"]?></title>
  <style>
    #comments {
      overflow-y: scroll;
      overflow-x: hidden;
      height: 600px;
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
      margin: 0 3px;
      padding: .375rem .1rem;
    }
    #live-name {
      font-weight: 600;
    }
  </style>
</head>
<body>
<?php $navmode = "fluid"; include "../include/navbar.php"; ?>
<div class="container-fluid">
  <div class="row">
    <div class="col-md-9">
      <div id="err_live" class="text-warning"></div>
      <div id="is_not_started" class="invisible">* この配信はまだ開始されていません。現在はあなたのみ視聴できます。<a href="<?=u("live_manage")?>">配信開始はこちらから</a></div>
      <?php if ($my["id"] === $live["user_id"]) : ?>
        <div class="text-warning">* これは自分の放送です。ハウリング防止の為自動でミュートしています。</div>
      <?php endif; ?>
      <div class="embed-responsive embed-responsive-16by9" id="live">
        <iframe class="embed-responsive-item" src="<?=u("live_embed")?>?id=<?=$id?>&rtmp=<?=$slot["server"]?>" allowfullscreen id="iframe"></iframe>
      </div>
      <span style="float: right">
          <span id="h"></span><span id="m"></span><span id="s"></span>
          <span id="count_open">
            視聴者数: <b id="count"><?=$live["viewers_count"]?></b> / <span class="max"><?=$live["viewers_max"]?></span>
          </span>
          <span id="count_end" class="invisible">
            総視聴者数(仮): <span class="max"><?=$live["viewers_max"]?></span>人 · 最大同時視聴者数: <span id="max_c"><?=$live["viewers_max_concurrent"]?></span>人
          </span>
        </span>
      <br>
      <div style="float: right">
        <button type="button" class="btn btn-link side-buttons" onclick="share()"><i class="fas fa-share-square"></i> 共有</button>
      </div>
      <p></p>
      <h4 id="live-name"><?=$live["name"]?></h4>
      <p>
        <img src="<?=$liveUser["misc"]["avatar"]?>" class="avatar_img_navbar rounded-circle"/>
        <?=$liveUser["name"]?><br>
        <small>総視聴者数: <?=$liveUser["misc"]["viewers_max"]?>人 · 最高同時視聴者数: <?=$liveUser["misc"]["viewers_max_concurrent"]?>人</small>
      </p>
      <p id="live-description"><?=nl2br($live["description"])?></p>
    </div>
    <div class="col-md-3">
      <div>
        <?php if ($my) : ?>
          <div class="form-group">
            <textarea class="form-control" id="toot" rows="3" placeholder="コメント... (<?=$my["acct"]?>としてトゥート)" onkeyup="check_limit()"></textarea>
          </div>
          <div class="input-group">
            <button class="btn btn-outline-primary" onclick="post_comment()">コメント</button>　<b id="limit"></b>
          </div>
        <?php else : ?>
          <p>
            <span class="text-warning">* コメントを投稿するにはログインしてください。<?=(!$liveUser["misc"]["live_toot"] ? "<br><br>{$env["masto_login"]["domain"]}のアカウントにフォローされているアカウントから#knzklive_{$id}をつけてトゥートしてもコメントする事ができます。" : "")?></span>
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

<script id="comment_tmpl" type="text/html">
  <div id="post_<%=id%>">
    <div class="row">
      <div class="col-2">
        <img src="<%=account['avatar']%>" class="avatar_img_navbar rounded-circle"/>
      </div>
      <div class="col-10">
        <b><%=account['display_name']%></b> <small>@<%=account['acct']%></small> <%=(me ? `<a href="#" onclick="delete_comment('${id}')">削除</a>` : "")%>
        <%=content%>
      </div>
    </div>
    <hr>
  </div>
</script>
<?php include "../include/footer.php"; ?>
<script src="js/tmpl.min.js"></script>
<script src="js/knzklive.js"></script>
<script>
  const hashtag_o = "knzklive_<?=$id?>";
  const hashtag = " #" + hashtag_o;
  const inst = "<?=$env["masto_login"]["domain"]?>";
  const token = "<?=$my ? s($_SESSION["token"]) : ""?>";
  var heartbeat, cm_ws, watch_data = {};
  var api_header = {'content-type': 'application/json'};
  if (token) api_header["Authorization"] = 'Bearer ' + token;

  const config = {
    "live_toot": <?=$liveUser["misc"]["live_toot"] ? "true" : "false"?>
  };

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
        elemId("count_open").className = "invisible";
        elemId("count_end").className = "";
        if (watch_data["live_status"] !== 0) document.getElementById('iframe').src = "<?=u("api/client/live_ended")?>";
      }
      if (json["live_status"] === 2 && watch_data["live_status"] !== 2) reloadLive();

      elemId("is_not_started").className = json["is_started"] ? "invisible" : "text-warning";

      if (json["name"] !== watch_data["name"]) {
        elemId("live-name").innerHTML = json["name"];
        elemId("title-name").innerHTML = json["name"] + ` - <?=$env["Title"]?>`;
      }
      if (json["description"] !== watch_data["description"]) elemId("live-description").innerHTML = json["description"];

      if (json["viewers_count"] !== watch_data["viewers_count"]) elemId("count").innerHTML = json["viewers_count"];
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
    }).then(function(response) {
      if (response.ok) {
        return response.json();
      } else {
        throw response;
      }
    }).then(function(json) {
    }).catch(function(error) {
      console.error(error);
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

  function loadComment() {
    elemId("err_comment").className = "invisible";

    fetch('https://' + inst + '/api/v1/timelines/tag/' + hashtag_o, {
      headers: api_header,
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
      if (json) {
        var reshtml = "";
        var ws_url = 'wss://' + inst + '/api/v1/streaming/?stream=hashtag&tag=' + hashtag_o;
        if (token) ws_url += "&access_token=" + token;

        cm_ws = new WebSocket(ws_url);
        cm_ws.onopen = function() {
          heartbeat = setInterval(() => cm_ws.send("ping"), 5000);
          cm_ws.onmessage = function(message) {
            var ws_resdata = JSON.parse(message.data);
            var ws_reshtml = JSON.parse(ws_resdata.payload);

            if (ws_resdata.event === 'update') {
              if (ws_reshtml['id']) {
                if (!ws_reshtml['application'] && config["live_toot"]) {
                  console.log('COMMENT BLOCKED', ws_reshtml);
                  return;
                }
                if (config["live_toot"] && (
                  ws_reshtml['application']['name'] !== "KnzkLive" ||
                  ws_reshtml['application']['website'] !== "https://<?=$env["domain"]?>" ||
                  ws_reshtml['account']['acct'] !== ws_reshtml['account']['username']
                )) {
                  console.log('COMMENT BLOCKED', ws_reshtml);
                  return;
                }
                let acct = ws_reshtml['account']['acct'] !== ws_reshtml['account']['username'] ? ws_reshtml['account']['acct'] : ws_reshtml['account']['username'] + "@" + inst;
                ws_reshtml["me"] = "<?=$my["acct"]?>" === acct;
                ws_reshtml["account"]["display_name"] = escapeHTML(ws_reshtml["account"]["display_name"]);
                elemId("comments").innerHTML = tmpl("comment_tmpl", ws_reshtml) + elemId("comments").innerHTML;
              }
            } else if (ws_resdata.event === 'delete') {
              var del_toot = elemId('post_' + ws_resdata.payload);
              if (del_toot) del_toot.parentNode.removeChild(del_toot);
            }
          };

          cm_ws.onclose = function() {
            clearInterval(heartbeat);
            loadComment();
          };
        };
        cm_ws.onerror = function() {
          console.warn('err:ws');
        };

        var i = 0;
        while (json[i]) {
          if (!json[i]['application'] && config["live_toot"]) {
            console.log('COMMENT BLOCKED', json[i]);
          } else {
            if (config["live_toot"] && (
              json[i]['application']['name'] !== "KnzkLive" ||
              json[i]['application']['website'] !== "https://<?=$env["domain"]?>" ||
              json[i]['account']['acct'] !== json[i]['account']['username']
            )) {
              console.log('COMMENT BLOCKED', json[i]);
            } else {
              let acct = json[i]['account']['acct'] !== json[i]['account']['username'] ? json[i]['account']['acct'] : json[i]['account']['username'] + "@" + inst;
              json[i]["me"] = "<?=$my["acct"]?>" === acct;
              json[i]["account"]["display_name"] = escapeHTML(json[i]["account"]["display_name"]);
              reshtml += tmpl("comment_tmpl", json[i]);
            }
          }
          i++;
        }

        elemId("comments").innerHTML = reshtml;
      }
    })
    .catch(error => {
      console.log(error);
      elemId("err_comment").className = "text-danger";
    });
  }

  function post_comment() {
    const v = elemId("toot").value;
    if (!v) return;
    fetch('https://' + inst + '/api/v1/statuses', {
      headers: api_header,
      method: 'POST',
      body: JSON.stringify({
        status: v + hashtag,
        visibility: "public"
      })
    })
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
      elemId("toot").value += "\n[投稿中にエラーが発生しました]";
    });
  }

  function delete_comment(_id) {
    fetch('https://' + inst + '/api/v1/statuses/' + _id, {
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
      elemId("toot").value += "\n[実行中にエラーが発生しました]";
    });
  }

  function check_limit() {
    if (!token) return; //未ログイン
    const l = elemId("limit");
    const d = elemId("toot").value;
    l.innerText = 500 - hashtag.length - d.length;
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


#KnzkLive #knzklive_<?=$live["id"]?>`;
      url = "web+mastodon://share?text=" + encodeURIComponent(text);
    } else if (mode === "facebook") {
      url = "https://www.facebook.com/sharer/sharer.php?u=<?=urlencode($liveurl)?>";
    } else if (mode === "line") {
      url = "http://line.me/R/msg/text/?<?=urlencode($liveurl)?>";
    } else if (mode === "weibo") {
      url = `http://service.weibo.com/share/share.php?url=<?=urlencode($liveurl)?>&title=` + encodeURIComponent(`${watch_data["name"]} by <?=$liveUser["name"]?> - KnzkLive`);
    }
    window.open(url);
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
      if (e.keyCode === 13 && e.ctrlKey) {
        post_comment()
      }
    });
  };
</script>
</body>
</html>