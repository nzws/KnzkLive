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
            * You can view recent 1000 logs.
        </p>

        <table class="table table-striped">
            <thead>
            <tr>
                <th>Transaction ID</th>
                <th>Created by</th>
                <th>Created at</th>
                <th>Type</th>
                <th>Point</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach (db_get('SELECT * FROM point_log ORDER BY id DESC LIMIT 1000') as $item) : ?>
                <tr>
                    <td><a href="<?=u('admincp/point_transaction_info')?>?id=<?=$item['id']?>"><?=s($item['id'])?></a></td>
                    <td><a href="<?=u('admincp/account_info')?>?id=<?=$item['user_id']?>"><?=s(getUser($item['user_id'])['acct'])?></a></td>
                    <td><?=$item['created_at']?></td>
                    <td><?=$item['type']?></td>
                    <td><?=$item['point']?></td>
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

