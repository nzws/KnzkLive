<?php require_once "../../lib/admincploader.php"; ?>

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
        <p>
            * You can view recent 1000 streams.
        </p>

        <table class="table table-striped">
            <thead>
            <tr>
                <th>Started at</th>
                <th>Created by</th>
                <th>Status</th>
                <th>Watching</th>
                <th>Visibility</th>
                <th>Server</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach (db_get('SELECT * FROM live ORDER BY id DESC LIMIT 1000') as $item) : ?>
                <tr>
                    <td><a href="<?=u('admincp/stream_info')?>?id=<?=$item['id']?>"><?=s($item['created_at'])?></a></td>
                    <td><a href="<?=u('admincp/account_info')?>?id=<?=$item['user_id']?>"><?=s(getUser($item['user_id'])['acct'])?></a></td>
                    <td><?=status_localize($item['is_live'], $item['is_started'])?></td></td>
                    <td><?=s($item['viewers_count'])?> / <?=s($item['viewers_max'])?></td>
                    <td><?=visibility_localize($item['privacy_mode'])?></td>
                    <td><?=s($item['slot_id'])?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include "../../include/footer.php"; ?>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs4/dt-1.10.18/datatables.min.css"/>
<script type="text/javascript" src="https://cdn.datatables.net/v/bs4/dt-1.10.18/datatables.min.js"></script>
<script>
    $(document).ready(function() {
        $('table').DataTable({
            order: [[0, 'desc']]
        });
    });
</script>
</body>
</html>

