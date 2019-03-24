<?php
require_once("../lib/bootloader.php");
$user = getUser($_GET["id"], "broadcaster_id");
if (empty($user)) {
    http_response_code(404);
    exit("ERR:このユーザーは存在しません。");
}

$new_live = ($user["live_current_id"]) ? getLive($user["live_current_id"]) : null;
if (!empty($new_live) && ($new_live["privacy_mode"] !== 1 || $new_live["is_started"] !== 1)) {
    $new_live = null;
}
?>
<!doctype html>
<html lang="ja" data-page="user">
<head>
  <?php include "../include/header.php"; ?>
  <title><?=$user["name"]?> のユーザーページ - <?=$env["Title"]?></title>

  <meta property="og:title" content="<?=$user["name"]?> のユーザーページ"/>
  <meta property="og:type" content="website"/>
  <meta content="summary" property="twitter:card" />
  <meta property="og:url" content="<?=userUrl($user["broadcaster_id"])?>"/>
  <meta property="og:image" content="<?=$user["misc"]["avatar"]?>"/>
  <meta property="og:site_name" content="<?=$env["Title"]?>"/>
  <meta property="og:description" content="<?=$user["name"]?> (<?=$user["acct"]?>) のユーザーページ"/>
  <meta name="description" content="<?=$user["name"]?> (<?=$user["acct"]?>) のユーザーページ">
</head>
<body>
<?php include "../include/navbar.php"; ?>
<div class="container">
  <a href="<?=$user["misc"]["user_url"]?>" class="jumb_link" target="_blank">
    <div class="jumbotron jumbotron-fluid" style="background-image: url('<?=$user["misc"]["header"]?>')">
      <div class="container">
        <div class="row">
          <div class="col-sm-3 text-center">
            <img src="<?=$user["misc"]["avatar"]?>" class="mr-3 avatar"/>
          </div>
          <div class="col-sm-9 text-center text-sm-left">
            <h2><?=$user["name"]?></h2>
            <?=$user["acct"]?>
            <p class="lead">
              <?php if (!empty($user["broadcaster_id"])) : ?>
                <span class="badge badge-warning">配信者</span>
              <?php endif; ?>
              <?php if (is_admin($user["id"])) : ?>
                <span class="badge" style="background-color: purple;">ADMIN</span>
              <?php endif; ?>
            </p>
          </div>
        </div>
      </div>
    </div>
  </a>
  <?php if (!empty($new_live)) : ?>
    <div class="alert alert-info" role="alert">
      <h4>この配信者は現在配信中です！</h4>
      <a href="<?=liveUrl($new_live["id"])?>"><b><?=$new_live["name"]?></b></a> <small>(<?=date("Y/m/d H:i", strtotime($new_live["created_at"]))?> に開始)</small>
    </div>
  <?php endif; ?>
  <h3>最近の配信履歴</h3>
  <div class="row">
    <?php
    foreach (getUserLives($user["id"]) as $item) {
        $url = liveUrl($item["id"]);
        $date = date("Y/m/d H:i", strtotime($item["created_at"]));
        echo <<< EOF
<div class="col-md-3 live">
<a href="{$url}">
<div class="card">
  <div class="card-body">
    <h5 class="card-title">{$item["name"]}</h5>
    <h6 class="card-subtitle mb-2 text-muted">{$date} に開始</h6>
    <p class="card-text">{$item["description"]}</p>
  </div>
</div>
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
