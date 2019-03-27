<?php
require_once "../lib/bootloader.php";
$dbstatus = !!db_start(true)->connect_errno;

$worker = file_get_contents($env["websocket_url"] . "/health", false);
$worker = json_decode($worker, true);

$ok = '<span class="badge badge-success">OK</span>';
$err = '<span class="badge badge-danger">ERROR</span>';
?>
<!doctype html>
<html lang="ja">
<head>
    <?php include "../include/header.php"; ?>
    <title>サービスステータス - <?=$env["Title"]?></title>
</head>
<body>
<?php include "../include/navbar.php"; ?>
<div class="container">
    <div class="box">
        <h4>KnzkLive サービスステータス</h4>
        <!--
        <div class="alert alert-success" role="alert">
            <b>All Systems Operational.</b> 全てのシステムが正常に稼働しています。
        </div>
      -->
        <div class="table-responsive">
            <table class="table">
                <thead>
                <tr>
                    <th></th>
                    <th>状態</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>Main System</td>
                    <td><?=$ok?></td> <!-- 死んでたらそもそも見れないよねっていう適当仕様 -->
                </tr>
                <tr>
                    <td>Database</td>
                    <td><?=($dbstatus ? $err : $ok)?></td>
                </tr>
                <tr>
                    <td>Detecting Worker</td>
                    <td><?=($worker ? $ok : $err)?> (<?=($worker["detect"] ? date("m-d H:i:s", $worker["detect"]) : "")?>)</td>
                </tr>
                <tr>
                    <td>TIPKnzk Worker</td>
                    <td><?=($worker ? $ok : $err)?> (<?=($worker["tipknzk"] ? date("m-d H:i:s", $worker["tipknzk"]) : "")?>)</td>
                </tr>
                <tr>
                    <td>Streaming Worker</td>
                    <td><?=($worker ? $ok : $err)?> (<?=($worker["streaming"] ? date("m-d H:i:s", $worker["tipknzk"]) : "")?>)</td>
                </tr>
                <!--
                <tr>
                    <td>Broadcasting Server #1</td>
                    <td><span class="badge badge-success">OK:STANDBY</span></td> OK:WORKING
                </tr>
                -->
            </table>
        </div>
</div>
    </div>
    <hr>
</div>

<?php include "../include/footer.php"; ?>
</body>
</html>
