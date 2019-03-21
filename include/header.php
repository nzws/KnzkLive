<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<link rel="shortcut icon" type="image/x-icon" href="<?=assetsUrl()?>static/favicon.ico">

<meta name="application-name" content="<?=$env["Title"]?>">
<meta name="msapplication-TileColor" content="#000000">
<meta name="theme-color" content="#000000">

<link rel="stylesheet" href="<?=assetsUrl()?>bundle/bundle.css?t=<?=filemtime(__DIR__ . "/../public/bundle/bundle.css")?>">
<script src="<?=assetsUrl()?>bundle/bundle.js?t=<?=filemtime(__DIR__ . "/../public/bundle/bundle.js")?>"></script>
<script>
  window.config = {
    root_url: "<?=$env["RootUrl"]?>",
    endpoint: "<?=$env["RootUrl"]?>api/",
    suffix: "<?=$env["is_testing"] ? ".php" : ""?>",
    csrf_token: "<?=$_SESSION['csrf_token']?>",
    main_domain: "<?=$env["masto_login"]["domain"]?>",
    is_debug: <?=$env["is_testing"] ? "true" : "false"?>,
    storage_url: "<?=$env["storage"]["root_url"]?>",
<?php if ($my = getMe()) : ?>
    account: {
      id: <?=$my["id"]?>,
      domain: "<?=s($_SESSION["login_domain"])?>",
      acct: "<?=$my["acct"]?>",
      token: "<?=s($_SESSION["token"])?>"
    }
<?php endif; ?>
  };
</script>
