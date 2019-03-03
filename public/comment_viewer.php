<?php
require_once("../lib/bootloader.php");
$my = getUser($_GET["id"]);
if (!$my || !$my["live_current_id"]) {
  http_response_code(404);
  exit("ERR:このユーザーは存在しないか、配信していません。ブラウザソースをリロードしてください。");
}

$live = getLive($my["live_current_id"]);
if (!$live) {
  http_response_code(404);
  exit("ERR:この配信は存在しません。");
}

$liveUser = getUser($live["user_id"]);
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>KnzkLive Comment-Viewer</title>
    <meta name="robots" content="noindex">
    <link rel="stylesheet" href="<?=assetsUrl()?>bundle/comment_viewer.css?t=<?=filemtime(__DIR__ . "/bundle/comment_viewer.css")?>">
    <script src="<?=assetsUrl()?>bundle/bundle.js?t=<?=filemtime(__DIR__ . "/../public/bundle/bundle.js")?>"></script>
    <script>
      window.config = {
        endpoint: "<?=$env["RootUrl"]?>api/",
        suffix: "<?=$env["is_testing"] ? ".php" : ""?>",
        csrf_token: "<?=$_SESSION['csrf_token']?>",
        main_domain: "<?=$env["masto_login"]["domain"]?>",
        is_debug: <?=$env["is_testing"] ? "true" : "false"?>,
        live: {
          id: <?=$live["id"]?>,
          hashtag_o: "<?=liveTag($live)?>",
          created_at: "<?=dateHelper($live["created_at"])?>",
          websocket_url: "<?=($env["is_testing"] ? "ws://localhost:3000/api/streaming" : "wss://" . $env["domain"] . $env["RootUrl"] . "api/streaming")?>/live/<?=s($live["id"])?>",
          watch_data: {},
          websocket: {},
          heartbeat: {},
          page: "comment_viewer"
        }
      };

      window.onload = function() {
        knzk.comment_viewer.ready();
      };
    </script>
</head>

<body>
<p class="invisible" id="err_comment">
  * コメントの読み込み中にエラーが発生しました。
</p>
<div id="comments"></div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/handlebars.js/4.0.12/handlebars.min.js" integrity="sha256-qlku5J3WO/ehJpgXYoJWC2px3+bZquKChi4oIWrAKoI=" crossorigin="anonymous"></script>

<script id="com_tmpl" type="text/x-handlebars-template">
  <div id="post_{{id}}" class="com">
    {{#if donator_color}}
      <span class="badge badge-pill" style="background:{{donator_color}}">
    {{/if}}
    <b>{{account.display_name}}</b>
    {{#if donator_color}}
      </span>
    {{/if}}

    {{{content}}}
  </div>
</script>
</body>

</html>
