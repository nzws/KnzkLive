<?php
require_once("../../../../lib/bootloader.php");
require_once("../../../../lib/apiloader.php");

$my = getMe();
if (!$my)
  api_json(["error" => "エラー: ログインしてください。"]);

api_json(get_point_log($my["id"], "hist", $_GET["page"]));
