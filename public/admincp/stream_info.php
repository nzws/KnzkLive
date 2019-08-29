<?php
require_once "../../lib/admincploader.php";
$live = getLive(s($_GET["id"]));

if (!$live) {
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
        <a href="<?=liveUrl($live["id"])?>" target="_blank">
        <?php if (empty($live["misc"]["exist_thumbnail"])) : ?>
            <img src="<?=assetsUrl()?>static/thumbnail.png" class="stream-thumbnail"/>
        <?php else : ?>
            <img src="<?=$env['storage']['root_url']?>thumbnail/<?=$live["id"]?>.png" class="stream-thumbnail"/>
        <?php endif; ?>
        </a>

        <table class="table table-striped mt-4 mb-4">
            <tbody>
            <tr><td>Name</td><td><?=s($live['name'])?></td></tr>
            <tr><td>Description</td><td><?=s($live['description'])?></td></tr>
            <tr><td>Created by</td><td><a href="<?=u('admincp/account_info')?>?id=<?=$live['user_id']?>"><?=s(getUser($live['user_id'])['acct'])?></a></td></tr>
            <tr><td>Started at</td><td><?=$live['created_at']?></td></tr>
            <tr><td>Ended at</td><td><?=$live['ended_at']?></td></tr>
            <tr><td>Status</td><td><?=status_localize($live['is_live'], $live['is_started'])?></td></tr>
            <tr><td>Visibility</td><td><?=visibility_localize($live['privacy_mode'])?></td></tr>
            <tr><td>Watching</td><td><?=s($live['viewers_count'])?> / <?=s($live['viewers_max'])?> (concurrent: <?=s($live['viewers_max_concurrent'])?>)</td></tr>
            <tr><td>Point</td><td><?=$live['point_count']?>KP</td></tr>
            <tr><td>Comment</td><td><?=$live['comment_count']?></td></tr>
            <tr><td>Hashtag</td><td>#<?=liveTag($live)?></td></tr>
            <tr><td>IP</td><td><?=$live['ip']?></td></tr>
            <tr><td>Sensitive?</td><td><?=$live['misc']['is_sensitive'] ? 'YES' : 'NO'?> <button class="btn btn-sm btn-warning">Change</button></td></tr>
            <tr><td>Item?</td><td><?=$live['misc']['able_item'] ? 'OK' : 'NG'?> <button class="btn btn-sm btn-warning">Change</button></td></tr>
            <tr><td>Comment?</td><td><?=$live['misc']['able_comment'] ? 'OK' : 'NG'?> <button class="btn btn-sm btn-warning">Change</button></td></tr>
            </tbody>
        </table>

        <h4>Collaborators</h4>
        <table class="table table-striped mb-4">
            <thead>
            <tr>
                <th>Account</th>
                <th>Status</th>
                <th>Server</th>
                <th>Commands</th>
            </tr>
            </thead>
            <tbody>
            <?php if (isset($live['misc']['collabo'])) : foreach ($live['misc']['collabo'] as $key => $value) : ?>
                <tr>
                    <td><a href="<?=u('admincp/account_info')?>?id=<?=s($key)?>"><?=s(getUser($key)['acct'])?></a></td>
                    <td><?=status_localize($value['status'], 1)?></td>
                    <td><?=(isset($value['slot']) ? $value['slot'] : '(none)')?></td>
                    <td><button class="btn btn-sm btn-danger">Delete</button></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>

        <?php if ($live['is_live'] === 2) : ?>
            <button class="btn btn-primary">Re-take thumbnail</button>
        <?php endif; ?>
        <?php if ($live['is_live'] === 0) : ?>
            <button class="btn btn-danger">Delete broadcast data</button>
        <?php else : ?>
            <button class="btn btn-danger">Forcibly stop broadcast</button>
        <?php endif; ?>
    </div>
</div>
<?php include "../../include/footer.php"; ?>
</body>
</html>
