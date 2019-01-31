<?php
require_once("../../../lib/bootloader.php");
require_once("../../../lib/apiloader.php");

$my = getMe();
$user_id = null;
if (!empty($my) && !$my["misc"]["hide_watching_list"])
  $user_id = $my["id"];

updateWatcher(s($_SERVER["REMOTE_ADDR"]), s($_GET["id"]), $user_id);
checkLeftUsers();

api_json(["ok" => true]);
