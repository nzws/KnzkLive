<?php
require_once("../../../lib/bootloader.php");
$live = getLive(s($_GET["id"]));
if (!$live) {
  http_response_code(404);
  exit("ERR:この配信は存在しません。");
}
$liveUser = getUser($live["user_id"]);
?>
<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>KnzkLive Comment-Viewer</title>
  <meta name="robots" content="noindex">
  <style>
    body {
      /* thx https://qiita.com/NoxGit/items/eb0904822c0f0fe97650 */
      text-shadow:
        black 2px 0,  black -2px 0,
        black 0 -2px, black 0 2px,
        black 2px 2px , black -2px 2px,
        black 2px -2px, black -2px -2px,
        black 1px 2px,  black -1px 2px,
        black 1px -2px, black -1px -2px,
        black 2px 1px,  black -2px 1px,
        black 2px -1px, black -2px -1px;

      background: transparent;
      color: #ffffff;
      font-family: -apple-system, BlinkMacSystemFont, "Helvetica Neue", YuGothic, "ヒラギノ角ゴ ProN W3", Hiragino Kaku Gothic ProN, Arial, "メイリオ", Meiryo, sans-serif;
    }

    a,
    a:hover {
      color: #fff;
      text-decoration: none;
    }

    p {
      margin: 0;
      margin-top: 10px;
    }

    .invisible {
      display: none;
    }

    .com {
      margin-top: 20px;
    }

    .hashtag {
      display: none;
    }
  </style>
</head>
<body>
<p class="invisible" id="err_comment">
  * コメントの読み込み中にエラーが発生しました。
</p>
<div id="comments"></div>
<script src="../../js/tmpl.min.js"></script>
<script src="../../js/knzklive.js"></script>
<script id="comment_tmpl" type="text/html">
  <div id="post_<%=id%>" class="com">
    <b><%=account['display_name']%></b> <small>@<%=account['acct']%></small>
    <%=content%>
  </div>
</script>
<script>
  const hashtag_o = "<?=liveTag($live)?>";
  const inst = "<?=$env["masto_login"]["domain"]?>";
  var api_header = {'content-type': 'application/json'};
  let io, w_heartbeat;

  const config = {
    "live_toot": <?=$liveUser["misc"]["live_toot"] ? "true" : "false"?>
  };

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

      io = new WebSocket("<?=($env["is_testing"] ? "ws://localhost:3000" : $env["RootUrl"] . "api/streaming")?>");
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
        if (data.event === "knzklive_comment_<?=s($live["id"])?>") {
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
          while (json[i]) {
            json[i]["account"]["display_name"] = escapeHTML(json[i]["account"]["display_name"]);
            reshtml += tmpl("comment_tmpl", json[i]);
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
        ws_reshtml["account"]["display_name"] = escapeHTML(ws_reshtml["account"]["display_name"]);
        elemId("comments").innerHTML = tmpl("comment_tmpl", ws_reshtml) + elemId("comments").innerHTML;
      }
    } else if (ws_resdata.event === 'delete') {
      var del_toot = elemId('post_' + ws_resdata.payload);
      if (del_toot) del_toot.parentNode.removeChild(del_toot);
    }
  }

  window.onload = function () {
    loadComment();
  };
</script>
</body>
</html>
