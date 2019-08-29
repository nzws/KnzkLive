<?php
require_once "../../lib/admincploader.php";

$lives = getAllLive(true);
if (isset($lives[0])) {
    $live_count = count($lives);
    $viewers_count = array_sum(array_column($lives, 'viewers_count'));
} else {
    $live_count = 0;
    $viewers_count = 0;
}

$loadavg = sys_getloadavg();
?>

<!doctype html>
<html lang="ja" data-page="admin">
<head>
    <?php include "../../include/header.php"; ?>
    <title>AdminCP - <?=$env["Title"]?></title>
</head>
<body>

<div class="container-fluid row mt-5 mb-5">
    <?php include '../../include/admincp/navbar.php'; ?>
    <div class="col-sm-9">
        <div class="row">
            <div class="col-md-4">
                <div class="alert alert-primary">
                    <div class="head">
                        <span class="number"><?=$live_count?></span>
                        users
                    </div>
                    <?=i('broadcast-tower')?> Broadcasting now
                </div>
            </div>

            <div class="col-md-4">
                <div class="alert alert-primary">
                    <div class="head">
                        <span class="number"><?=$viewers_count?></span>
                        users
                    </div>
                    <?=i('tv')?> Watching now
                </div>
            </div>

            <div class="col-md-4">
                <div class="alert alert-primary">
                    <div class="head">
                        <span class="number"><?=db_count('users')?></span>
                        users
                    </div>
                    <?=i('users')?> Registered
                </div>
            </div>

            <div class="col-md-4">
                <div class="alert alert-primary">
                    <div class="head">
                        <span class="number"><?=db_count('items')?></span>
                        files
                    </div>
                    <?=i('cloud-upload-alt')?> Uploaded
                </div>
            </div>

            <div class="col-md-4">
                <div class="alert alert-primary">
                    <div class="head">
                        <span class="number"><?=db_get('select SUM(point_count) as p from users')[0]['p']?></span>
                        KP
                    </div>
                    <?=i('wallet')?> Total
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <table class="table table-sm">
                    <thead>
                    <tr>
                        <th>Main Server info</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr><td>Loadavg 1m</td><td><?=$loadavg[0]?></td></tr>
                    <tr><td>Loadavg 5m</td><td><?=$loadavg[1]?></td></tr>
                    <tr><td>Loadavg 15m</td><td><?=$loadavg[2]?></td></tr>
                    <tr><td>Hostname</td><td><?=$_SERVER['SERVER_NAME']?></td></tr>
                    <tr><td>OS</td><td><?=php_uname('s')?> <?=php_uname('r')?></td></tr>
                    <tr><td>Software</td><td><?=$_SERVER['SERVER_SOFTWARE']?></td></tr>
                    <tr><td>Database</td><td><?=db_get('SELECT version() as v')[0]['v']?></td></tr>
                    <tr><td>Mastodon</td><td><?=$env['masto_login']['domain']?></td></tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
<?php include "../../include/footer.php"; ?>
</body>
</html>

