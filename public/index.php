<?php
require_once "../lib/bootloader.php";
$lives = getAllLive();
if (isset($lives[0])) {
    $live_count = count($lives);
    $viewers_count = array_sum(array_column($lives, 'viewers_count'));
}
?>
<!doctype html>
<html lang="ja" data-page="index">
<head>
    <?php include "../include/header.php"; ?>
    <title><?=$env["Title"]?></title>
    <script>
        window.onload = () => {
            knzk.settings.general.loadMoment();
        };
    </script>
</head>
<body>
<?php include "../include/navbar.php"; ?>

<div class="container mt-3">
    <?php if (empty($_SESSION["acct"])) : ?>
        <div class="about">
            <button type="button" class="close text-white" onclick="hideAbout()">
                <span aria-hidden="true">&times;</span>
            </button>
            <h2>KnzkLive</h2>
            <?=isset($env["top_about"]) ? $env["top_about"] : $env["Title"]?>
        </div>
        <hr class="mb-4">
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-8">
            <h2><?=i("broadcast-tower")?> ライブ</h2>
            <?php if (isset($lives[0])) : ?>
            <h5>KnzkLiveで <?=$live_count?>人が配信中, <?=$viewers_count?>人が視聴中</small></h5>
            <div class="row mt-3">
                <?php foreach ($lives as $live) : $liveUser = getUser($live["user_id"]); ?>
                    <div class="col-md-6 live_box">
                        <a href="<?=liveUrl($live["id"])?>" class="nodeco_link">
                            <div class="live_thumbnail mb-2">
                                <?php if (empty($live["misc"]["exist_thumbnail"])) : ?>
                                    <img src="<?=assetsUrl()?>static/thumbnail.png" class="img-fluid rounded border border-dark"/>
                                <?php else : ?>
                                    <img src="<?=$env['storage']['root_url']?>thumbnail/<?=$live["id"]?>.png" class="img-fluid rounded border border-dark"/>
                                <?php endif; ?>
                                <div class="status">
                                    <?=i("clock")?> <span class="momentjs" data-time="<?=s($live["created_at"])?>" data-type="fromNow"></span>
                                    <span class="ml-3"><?=i("comments")?> <?=s($live["comment_count"])?></span>
                                    <span class="ml-3"><?=i("users")?> <?=$live["viewers_count"]?> / <?=$live["viewers_max"]?></span>
                                </div>
                            </div>
                            <h4><?=$live["name"]?></h4>
                        </a>
                        <a href="<?=userUrl($liveUser["broadcaster_id"])?>" class="nodeco_link">
                            <img src="<?=$liveUser["misc"]["avatar"]?>" class="live_user_icon rounded float-left mr-2"/>
                            <p>
                                <b><?=$liveUser["name"]?></b><br>
                                <small>@<?=$liveUser["acct"]?></small>
                            </p>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php else : ?>
            <div class="text-center mt-4 mb-4">
                <img src="<?=assetsUrl()?>static/surprized_knzk.png" class="knzk mb-2"/>
                <h3 class="text-secondary">現在、生放送中の配信はありません</h3>
            </div>
            <?php endif; ?>
        </div>
        <div class="col-lg-4">
            <h4 class="mb-2"><?=i("history")?> 最近配信したユーザ</h4>
            <ul class="users">
                <?php foreach (getLastLives() as $live) : $liveUser = getUser($live["user_id"]); ?>
                    <li>
                        <a href="<?=userUrl($liveUser["broadcaster_id"])?>" class="nodeco_link">
                            <img src="<?=$liveUser["misc"]["avatar"]?>" class="rounded float-left mr-2"/>
                            <p>
                                <b><?=$liveUser["name"]?></b><br>
                                <span class="text-secondary momentjs mr-1" data-time="<?=s($live["ended_at"])?>" data-type="fromNow"></span>
                                <?=$live["name"]?>
                            </p>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
            <hr>
            <h4 class="mb-2"><?=i("calendar")?> 予約された配信</h4>
            <ul>
                <!-- WIP -->
            </ul>
        </div>
    </div>

    <?php if (!empty($_SESSION["acct"])) : ?>
        <hr class="mt-4">
        <div class="about">
            <button type="button" class="close text-white" onclick="hideAbout()">
                <span aria-hidden="true">&times;</span>
            </button>
            <h2>KnzkLive</h2>
            <?=isset($env["top_about"]) ? $env["top_about"] : $env["Title"]?>
        </div>
    <?php endif; ?>
</div>
<script>
    if (localStorage['hide_about']) $('.about').hide();
    function hideAbout() {
        $('.about').hide();
        localStorage['hide_about'] = 1;
    }
</script>
<?php include "../include/footer.php"; ?>
</body>
</html>
