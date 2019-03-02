<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css" integrity="sha384-GJzZqFGwb1QTTN6wy59ffF1BuGJpLSa9DkKMp0DgiMDm4iYMj70gZWKYbI706tWS" crossorigin="anonymous">
<link rel="shortcut icon" type="image/x-icon" href="<?=$env["RootUrl"]?>static/favicon.ico">

<meta name="application-name" content="<?=$env["Title"]?>">
<meta name="msapplication-TileColor" content="#000000">
<meta name="theme-color" content="#000000">

<link rel="stylesheet" href="<?=$env["RootUrl"]?>bundle/style.css?t=<?=filemtime(__DIR__ . "/../public/bundle/style.css")?>">
<script src="<?=$env["RootUrl"]?>bundle/bundle.js?t=<?=filemtime(__DIR__ . "/../public/bundle/bundle.js")?>"></script>
<script>
  window.config = {
    endpoint: "<?=$env["RootUrl"]?>api/",
    suffix: "<?=$env["is_testing"] ? ".php" : ""?>",
    csrf_token: "<?=$_SESSION['csrf_token']?>",
    main_domain: "<?=$env["masto_login"]["domain"]?>",
    is_debug: <?=$env["is_testing"] ? "true" : "false"?>,
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
