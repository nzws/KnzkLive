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
            * You can view recent 1000 uploads.
        </p>

        <table class="table table-striped">
            <thead>
            <tr>
                <th>Created at</th>
                <th>Created by</th>
                <th>Name</th>
                <th>Type</th>
                <th>Point</th>
                <th>Commands</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach (db_get('SELECT * FROM items ORDER BY id DESC LIMIT 1000') as $item) : ?>
                <tr>
                    <td><?=s($item['created_at'])?></td>
                    <td><a href="<?=u('admincp/account_info')?>?id=<?=$item['user_id']?>"><?=s(getUser($item['user_id'])['acct'])?></a></td>
                    <td><?=s($item['name'])?></td></td>
                    <td><?=s($item['type'])?></td>
                    <td><?=s($item['point'])?></td>
                    <td><a href="<?=$env['storage']['root_url']?><?=s($item['type'])?>/<?=$item['file_name']?>" target="_blank">Download</a> / <a class="text-danger">Delete</a></td>
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

