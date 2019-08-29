<?php
require_once "../../lib/admincploader.php";
$user = getUser(s($_GET["id"]));

if (empty($user)) {
    showError('Not Found', 404);
}
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
        <a href="<?=s($user["misc"]["user_url"])?>" target="_blank">
            <img src="<?=s($user["misc"]["avatar"])?>" class="account-thumbnail"/>
        </a>

        <table class="table table-striped mt-4 mb-4">
            <tbody>
            <tr><td>Name</td><td><?=s($user['name'])?></td></tr>
            <tr><td>Account ID</td><td><?=s($user['acct'])?></td></tr>
            <tr><td>Broadcaster?</td><td><?php if (!empty($user['broadcaster_id'])) : ?>YES: <?=s($user['broadcaster_id'])?><?php else : ?>NO<?php endif; ?></td></tr>
            <tr><td>Twitter ID</td><td><?=(empty($user['twitter_id']) ? '(none)' : $user['twitter_id'])?></td></tr>
            <tr><td>Created at</td><td><?=$user['created_at']?></td></tr>
            <tr><td>Status</td><td>Active</td></tr>
            <tr><td>Point</td><td><?=$user['point_count']?>KP</td></tr>
            <tr><td>IP</td><td><?=$user['ip']?></td></tr>
            </tbody>
        </table>

        <button class="btn btn-danger">Suspend</button>
        <button class="btn btn-primary">Move account</button>
        <?php if (empty($user['broadcaster_id'])) : ?>
            <button class="btn btn-primary">Add broadcast permission</button>
        <?php else : ?>
            <button class="btn btn-primary">Change broadcaster ID</button>
        <?php endif; ?>
    </div>
</div>
<?php include "../../include/footer.php"; ?>
</body>
</html>
