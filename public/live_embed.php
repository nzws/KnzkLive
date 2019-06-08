<?php
require_once "../lib/bootloader.php";
$live = getLive(s($_GET["id"]));
if (!$live) {
    showError('この配信は存在しません。', 404);
}
$liveUser = getUser(isset($_GET["collabo"]) ? $_GET["collabo"] : $live["user_id"]);
if (!$liveUser) {
    showError('値が不正です。', 500);
}
$slot = getSlot(isset($_GET["collabo"]) ? $live["misc"]["collabo"][$_GET["collabo"]]["slot"] : $live["slot_id"]);

$my = getMe();
if (!$my && $live["privacy_mode"] === 3) {
    showError('この配信は非公開です。', 403);
}

$myLive = $my["id"] === $live["user_id"];
if (!$myLive && $live["is_started"] == "0") {
    showError('この配信はまだ開始されていません。', 403);
}

if (empty($_SESSION["watch_type"])) {
    // $_SESSION["watch_type"] = preg_match('/(iPhone|iPad)/', $_SERVER['HTTP_USER_AGENT']) ? "HLS" : "FLV";
    $_SESSION["watch_type"] = "HLS";
}
if (isset($_GET["watch_type"])) {
    $_SESSION["watch_type"] = $_GET["watch_type"] == 0 ? "FLV" : "HLS";
}
$mode = $_SESSION["watch_type"];

$stream = $live["id"] . "stream" . (isset($_GET["collabo"]) ? s($_GET["collabo"]) . "collabo" : "");
?>
<!DOCTYPE html>
<html data-page="live_embed">
<head>
    <meta name="robots" content="noindex">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="<?=assetsUrl()?>bundle/bundle.css?t=<?=filemtime(__DIR__ . "/bundle/bundle.css")?>">
</head>

<body>
<div id="play_button" style="display: none" onclick="knzk.live_embed.player.seekLive()">
    <b>[クリックして再生]</b><br>
    <small>(ブラウザが自動再生をブロックしました...)</small>
    <img src="<?=assetsUrl()?>static/surprized_knzk.png"/>
</div>

<div id="splash">
        <div id="splash_loadtext">接続しています...</div>
</div>

<div id="end_dialog" class="center_v" style="display: none">
        <img src="<?=assetsUrl()?>static/knzklive_logo.png" class="waiting_logo animated"/>
        <p>
            配信は終了しました。
        </p>
</div>

<div id="video">
    <video id="knzklive" class="center_v" autoplay preload="auto">
        <p>
            KnzkLive Playerからのお知らせ:<br>
            この環境では視聴する事ができません。OS・ブラウザをアップデートするか、別の環境からお試しください。
        </p>
    </video>
    <img src="<?=assetsUrl()?>static/knzklive_logo.png" class="watermark header"/>
    <div class="header live_user hover">
        <a href="<?=userUrl($liveUser["broadcaster_id"])?>" target="_blank" class="broadcaster_link">
            <img src="<?=$liveUser["misc"]["avatar"]?>"/>
            <b><?=$liveUser["name"]?></b>
        </a>
    </div>
    <div class="footer hover" style="background: rgba(0,0,0,.5)">
        <div class="footer_content">
            <span id="video_status">LOADING</span>
            <span class="right video_control">
                <a href=""><i class="fas fa-sync-alt fa-fw"></i></a>

                <span class="dropdown">
                    <a data-toggle="dropdown"><?=i('cogs')?></a>
                    <div class="dropdown-menu">
                        <!-- <a class="dropdown-item" href="#">コメントを表示 <?=i('check-square')?></a> -->
                        <a class="dropdown-item" href="?id=<?=$live["id"]?>&watch_type=<?=($mode === "HLS" ? 0 : 1)?>">配信モード: <b><?=s($mode)?></b></a>
                    </div>
                </span>

                <a href="javascript:knzk.live_embed.player.mute()" id="mute" class="invisible"><i class="fas fa-volume-mute fa-fw"></i></a>
                <a href="javascript:knzk.live_embed.player.mute(1)" id="volume"><i class="fas fa-volume-up fa-fw"></i></a>
                <span class="volume_controller">
                    <span style="margin-right: 8px"></span>
                    <input type="range" id="volume-range" onchange="knzk.live_embed.player.volume(this.value)">
                </span>

                <a href="javascript:parent.live.live.widemode()"><i class="fas fa-arrows-alt-h fa-fw"></i></a>
                <a href="javascript:knzk.live_embed.player.full()"><i class="fas fa-expand fa-fw"></i></a>
            </span>
        </div>
    </div>
</div>
<div id="item_layer"></div>
<div id="comment_layer"></div>

<script id="item_emoji_tmpl" type="text/x-handlebars-template">
    <div class="item_emoji {{class}}" style="{{style}}" id="{{random_id}}">
        {{repeat_helper}}
    </div>
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/handlebars.js/4.0.12/handlebars.min.js" integrity="sha256-qlku5J3WO/ehJpgXYoJWC2px3+bZquKChi4oIWrAKoI=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/flv.js/1.4.2/flv.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
<script src="<?=assetsUrl()?>bundle/bundle.js?t=<?=filemtime(__DIR__ . "/../public/bundle/bundle.js")?>"></script>
<script>
    window.video = document.getElementById("knzklive");

    window.config = {
        type: '<?=s($mode)?>',
        myLive: <?=$myLive ? "true" : "false"?>,
        test_flv: '<?=empty($env["test_flv_server"]) ? '' : $env["test_flv_server"]?>',
        flv: 'ws<?=(empty($_SERVER["HTTPS"]) ? "" : "s")?>://<?=$slot["server"]?>/live/<?=$stream?>.flv',
        hls: 'http<?=(empty($_SERVER["HTTPS"]) ? "" : "s")?>://<?=$slot["server"]?>/live/<?=$stream?>/index.m3u8',
        heartbeat: null,
        delay_sec: 3,
        hover: 0,
        play_suc_cnt: 0,
        play_err_cnt: 0
    };

    window.onload = function() {
        knzk.live_embed.ready();
    }
</script>
</body>
</html>
