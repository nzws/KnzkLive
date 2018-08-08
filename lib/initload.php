<?php
$ConfigVersion = 1;
$confpath = dirname(__FILE__)."/../config.php";
date_default_timezone_set('Asia/Tokyo');

if (file_exists($confpath)) {
    require_once($confpath);
    if (CONF_VERSION < $ConfigVersion) {
      http_response_code(500);
      exit("SERVER ERROR: Config file is older");
    }
} else {
    http_response_code(500);
    exit("SERVER ERROR: Config file is not found");
}

if ($env["is_maintenance"]) {
    http_response_code(503);
    //include PATH."public/errors/503.html";
    exit("Error: maintenance");
}

if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
  $_SERVER["REMOTE_ADDR"] = $_SERVER["HTTP_CF_CONNECTING_IP"];
}

session_start();
if (!isset($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = $env["is_debug"] ? "CSRF_TOKEN_FOR_DEBUG" : base64_encode(openssl_random_pseudo_bytes(32));
}

if ($_POST && (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token'])) {
  http_response_code(403);
  exit("ERROR: CSRF Challenge is failed");
}
$libpt = dirname(__FILE__)."/";

require_once($libpt."components.php");
require_once($libpt."db.php");
require_once($libpt."user.php");
require_once($libpt."live.php");