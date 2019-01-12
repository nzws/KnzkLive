<?php
require_once("../../lib/bootloader.php");

if (isset($_GET["denied"])) {
  exit("ERR:ログイン拒否されました");
}
$connection = new \Abraham\TwitterOAuth\TwitterOAuth($env["tw_login"]["key"], $env["tw_login"]["secret"]);
if (empty($_GET["oauth_verifier"])) {
  $res = $connection->oauth("oauth/request_token", ["oauth_callback" => $env["tw_login"]["redirect_uri"]]);
  if (empty($res["oauth_token"])) exit("ERR:トークンを受信できませんでした。(1)もう一度トップページからお試しください。");
  header("Location: " . $connection->url("oauth/authenticate", ["oauth_token" => $res["oauth_token"]]));
  exit();
} else {
  $access_token = $connection->oauth("oauth/access_token", ["oauth_verifier" => $_GET["oauth_verifier"], "oauth_token" => $_GET["oauth_token"]]);
  if (empty($access_token["oauth_token"]) || empty($access_token["oauth_token_secret"])) exit("ERR:トークンを受信できませんでした。(2)もう一度トップページからお試しください。");

  $connection = new \Abraham\TwitterOAuth\TwitterOAuth($env["tw_login"]["key"], $env["tw_login"]["secret"], $access_token["oauth_token"], $access_token["oauth_token_secret"]);
  $content = $connection->get("account/verify_credentials");
  $content = json_decode(json_encode($content), true);
  if (empty($content["id_str"])) exit("ERR: アカウント情報が取得できませんでした。");

  $mysqli = db_start();
  $name = s($content["name"]);
  $acct = s($content["screen_name"] . "@twitter.com");
  if ($user = getUser($content["id_str"], "twitter_id")) {
    $misc = $user["misc"];
    $misc["avatar"] = $content["profile_image_url_https"];
    $misc["header"] = $content["profile_banner_url"];
    $misc = json_encode($misc);

    $stmt = $mysqli->prepare("UPDATE `users` SET `name` = ?, `acct` = ?, `ip` = ?, `misc` = ?  WHERE `twitter_id` = ?;");
    $stmt->bind_param('sssss', $name, $acct, $_SERVER["REMOTE_ADDR"], $misc, $content["id_str"]);
  } else { //新規
    $misc["avatar"] = $content["profile_image_url_https"];
    $misc["header"] = $content["profile_banner_url"];
    $misc = json_encode($misc);

    $stmt = $mysqli->prepare("INSERT INTO `users` (`name`, `acct`, `created_at`, `ip`, `misc`, `twitter_id`) VALUES (?, ?, CURRENT_TIMESTAMP, ?, ?, ?);");
    $stmt->bind_param('sssss', $name, $acct, $_SERVER["REMOTE_ADDR"], $misc, $content["id_str"]);
  }
  $stmt->execute();
  $stmt->close();
  $mysqli->close();
  $_SESSION["token"] = $access_token["oauth_token"];
  $_SESSION["token_secret"] = $access_token["oauth_token_secret"];
  $_SESSION["acct"] = $acct;
  $_SESSION["account_provider"] = "twitter";
  $_SESSION["login_domain"] = "twitter.com";

  header("Location: ".$env["RootUrl"]);
}
