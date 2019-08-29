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
            * You can view recent 1000 users.
        </p>

        <table class="table table-striped">
            <thead>
            <tr>
                <th>ID</th>
                <th>Acct</th>
                <th>Created at</th>
                <th>Status</th>
                <th>Broadcaster?</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach (db_get('SELECT * FROM users ORDER BY id DESC LIMIT 1000') as $item) : ?>
                <tr>
                    <td><a href="<?=u('admincp/account_info')?>?id=<?=$item['id']?>"><?=s($item['id'])?></a></td>
                    <td><?=s($item['acct'])?></td>
                    <td><?=s($item['created_at'])?></td>
                    <td>Active</td>
                    <td><?=(empty($item['broadcaster_id']) ? 'NO' : 'YES')?></td>
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

