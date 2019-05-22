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

    <div class="container">
        <h2><?=i("broadcast-tower")?> ライブ</h2>
        <?php if (isset($lives[0])) : ?>
            <h5 class="text-muted">KnzkLiveで <?=$live_count?>人が配信中, <?=$viewers_count?>人が視聴中</small></h5>

            <?php foreach ($lives as $live) : $liveUser = getUser($live["user_id"]); ?>
                <div class="text_ellipsis media position-relative mb-4 live_box">
                    <div class="live_thumbnail mr-3">
                        <a href="<?=liveUrl($live["id"])?>" class="nodeco_link">
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
                        </a>
                    </div>

                    <div class="media-body">
                        <h4><a href="<?=liveUrl($live["id"])?>" class="nodeco_link"><?=$live["name"]?></a></h4>

                        <a href="<?=userUrl($liveUser["broadcaster_id"])?>" class="nodeco_link">
                            <img src="<?=$liveUser["misc"]["avatar"]?>" class="live_user_icon rounded float-left mr-2"/>
                            <p class="mb-1">
                                <b><?=$liveUser["name"]?></b><br>
                                <small class="text-muted">@<?=$liveUser["acct"]?></small>
                            </p>
                        </a>

                        <div class="text-secondary text_wrap">
                            <?=mb_substr(s($live["description"]), 0, 40)?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <div class="text-center mt-4 mb-4">
                <img src="<?=assetsUrl()?>static/surprized_knzk.png" class="knzk mb-2"/>
                <h3 class="text-secondary">現在、生放送中の配信はありません</h3>
            </div>
        <?php endif; ?>

        <hr class="mt-4 mb-4">

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
