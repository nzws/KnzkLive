<?php
require_once("../../../lib/bootloader.php");
require_once("../../../lib/apiloader.php");

$acct = getUser(s($_GET["acct"]), "acct");

if (!$acct) {
  api_json(["error" => "ユーザーが見つかりません"]);
}

api_json(["name" => $acct["name"], "acct" => $acct["acct"]]);
