<?php
require_once("../../../lib/bootloader.php");
require_once("../../../lib/apiloader.php");
header('Access-Control-Allow-Origin: *');

$live = getLive(s($_GET["id"]));

if (!$live) {
  api_json(["error" => "放送が見つかりません"]);
}

$live["description"] = nl2br($live["description"]);
api_json(live4Pub($live));