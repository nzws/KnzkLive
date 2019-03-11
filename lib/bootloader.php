<?php
$confpath = __DIR__ . "/../config.php";
date_default_timezone_set('Asia/Tokyo');
header('server: KnzkLive');
header('X-Powered-By: KnzkDev <3');

if (file_exists($confpath)) {
    require_once($confpath);
} else {
    http_response_code(500);
    exit("SERVER ERROR: Config file is not found");
}

if ($env["is_testing"]) {
  ini_set('display_errors', 1);
}

if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
  $_SERVER["REMOTE_ADDR"] = $_SERVER["HTTP_CF_CONNECTING_IP"];
}

session_start();
if (!isset($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_POST && (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token'])) {
  http_response_code(403);
  exit("ERROR: CSRF Challenge is failed");
}
$libpt = __DIR__ . "/";

if (!file_exists($libpt."../vendor/autoload.php")) {
  http_response_code(500);
  exit("SERVER ERROR: Please install composer deps");
}

require_once($libpt."../vendor/autoload.php");
require_once($libpt."components.php");
require_once($libpt."db.php");
require_once($libpt."user.php");
require_once($libpt."live.php");
require_once($libpt."check_watching.php");
require_once($libpt."comment.php");
require_once($libpt."mastodon_auth.php");
require_once($libpt."prop.vote.php");
require_once($libpt."point.php");
require_once($libpt."donate.php");
require_once($libpt."file.php");
require_once($libpt."items.php");

$toot_get_limit = 200;

if ($env["is_maintenance"]) {
  showError("現在メンテナンス中です。", 503);
}
