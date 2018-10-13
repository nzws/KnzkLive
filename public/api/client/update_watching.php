<?php
require_once("../../../lib/bootloader.php");
require_once("../../../lib/apiloader.php");

updateWatcher(s($_SERVER["REMOTE_ADDR"]), s($_GET["id"]));
checkLeftUsers();

api_json(["ok" => true]);