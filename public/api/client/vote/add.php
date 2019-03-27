<?php
require_once "../../../../lib/bootloader.php";
require_once "../../../../lib/apiloader.php";

addVote(s($_GET["id"]), s($_GET["type"]));
api_json(["ok" => true]);
