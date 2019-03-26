<?php
require_once("../lib/bootloader.php");
$lives = getAllLive();
?>
<!doctype html>
<html lang="ja" data-page="index">
<head>
    <?php include "../include/header.php"; ?>
    <title>トップ - <?=$env["Title"]?></title>
</head>
<body>
<?php include "../include/navbar.php"; ?>
<div class="container">
    <div class="top_lists">
        <?php if (!$lives[0]) : ?>
            <div class="no">
                <h4>現在、生放送中の配信はありません</h4>
            </div>
        <?php else : ?>
            <div class="container">
                <h3>KnzkLive: 只今配信中！</h3>
                <div class="row">
                    <?php
                    if ($lives) {
                        $i = 0;
                        while (isset($lives[$i])) {
                            $url = liveUrl($lives[$i]["id"]);
                            $liveUser = getUser($lives[$i]["user_id"]);
                            echo <<< EOF
<div class="col-md-3 card-base">
<a href="{$url}">
<div class="card">
    <div class="card-img-div">
        <img class="card-img-top" src="{$liveUser["misc"]["avatar"]}">
    </div>
    <div class="card-body">
        <h5 class="card-title">{$lives[$i]["name"]}</h5>
        <p class="card-text">by {$liveUser["name"]}</p>
    </div>
</div>
</a>
</div>
EOF;
                            $i++;
                        }
                    }
                    ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <hr>
</div>
<div class="container history" style="margin-top:10px">
    <h3>配信者一覧</h3>
    <div class="row">
        <?php
        foreach (getLastLives() as $item) {
            $liveUser = getUser($item["user_id"]);
            $url = userUrl($liveUser["broadcaster_id"]);
            echo <<< EOF
<div class="col-md-3 card-base">
<a href="{$url}">
<div class="text-center">
    <img class="card-img-top rounded" src="{$liveUser["misc"]["avatar"]}"/>
    <h4 class="name">{$liveUser["name"]}</h4>
</div>
<small>最後の配信:</small> {$item["name"]}
</a>
</div>
EOF;
        }
        ?>
    </div>
</div>

<?php include "../include/footer.php"; ?>
</body>
</html>
