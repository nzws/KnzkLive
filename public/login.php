<?php
require_once("../lib/bootloader.php");
$code = s($_GET["code"]);

$domain = s($_GET["domain"]);
if ($_SESSION["login_domain"] && !$domain) $domain = $_SESSION["login_domain"];
if (!$domain) exit("ERR: ドメインが入力されていません");
$_SESSION["login_domain"] = $domain;
$info = getMastodonAuth($domain);
if (!$info) {
  $client_data = post("https://".$domain."/api/v1/apps", [
    "scopes" => "read write",
    "client_name" => "KnzkLive",
    "redirect_uris" => $env["masto_login"]["redirect_uri"],
    "website" => "https://". $env["domain"]
  ]);
  if (!$client_data["client_id"] || !$client_data["client_secret"]) exit("ERR: Mastodonから取得できませんでした");
  setMastodonAuth($domain, $client_data["client_id"], $client_data["client_secret"]);
} else {
  $client_data["client_id"] = $info["client_id"];
  $client_data["client_secret"] = $info["client_secret"];
}

if (!$code) {
  header("Location: https://".$domain."/oauth/authorize?response_type=code&redirect_uri=".$env["masto_login"]["redirect_uri"]."&scope=read+write&client_id=".$client_data["client_id"]);
  exit();
} else {
  $data = [
    "client_id" => $client_data["client_id"],
    "client_secret" => $client_data["client_secret"],
    "grant_type" => "authorization_code",
    "redirect_uri" => $env["masto_login"]["redirect_uri"],
    "code" => $code
  ];

  $header = [
    'Content-Type: application/json'
  ];

  $options = array('http' => array(
    'method' => 'POST',
    'content' => json_encode($data),
    'header' => implode(PHP_EOL,$header)
  ));
  $options = stream_context_create($options);
  $contents = file_get_contents("https://".$domain."/oauth/token", false, $options);
  $json = json_decode($contents,true);
  if ($json["access_token"]) {
    $header = [
      'Authorization: Bearer '.$json["access_token"],
      'Content-Type: application/json'
    ];
    $options = array('http' => array(
      'method' => 'GET',
      'header' => implode(PHP_EOL,$header)
    ));
    $options = stream_context_create($options);
    $contents = file_get_contents("https://".$domain."/api/v1/accounts/verify_credentials", false, $options);
    $json_acct = json_decode($contents,true);
    $name = s($json_acct["display_name"]);
    if ($json_acct["id"]) {
      $mysqli = db_start();
      $acct = s($json_acct["acct"]."@".$domain);
      if ($user = getUser($acct, "acct")) {
        $misc = $user["misc"];
        $misc["avatar"] = $json_acct["avatar_static"];
        $misc["header"] = $json_acct["header_static"];
        $misc["user_url"] = $json_acct["url"];
        $misc = json_encode($misc);

        $stmt = $mysqli->prepare("UPDATE `users` SET `name` = ?, `ip` = ?, `misc` = ?  WHERE `acct` = ?;");
        $stmt->bind_param('ssss', $name, $_SERVER["REMOTE_ADDR"], $misc, $acct);
      } else { //新規
        $misc["avatar"] = $json_acct["avatar_static"];
        $misc["header"] = $json_acct["header_static"];
        $misc["user_url"] = $json_acct["url"];
        $misc = json_encode($misc);
        $stmt = $mysqli->prepare("INSERT INTO `users` (`id`, `name`, `acct`, `created_at`, `ip`, `misc`) VALUES (NULL, ?, ?, CURRENT_TIMESTAMP, ?, ?);");
        $stmt->bind_param('ssss', $name, $acct, $_SERVER["REMOTE_ADDR"], $misc);
        node_update_conf("add", "user", $acct, "none");
      }
      $stmt->execute();
      $stmt->close();
      $mysqli->close();
      $_SESSION["token"] = $json["access_token"];
      $_SESSION["acct"] = $acct;
      $_SESSION["account_provider"] = "mastodon";

      header("Location: ".$env["RootUrl"]);
    } else {
      err(2, $contents);
    }
  } else {
    err(1, $contents);
  }
}

function err($type, $data) {
  http_response_code(500);
  var_dump($data);
  exit("ERR:Mastodonからデータが取得できませんでした:".$type);
}

function post($url, $data) {
  $header = [
    'Content-Type: application/json'
  ];

  $options = array('http' => array(
    'method' => 'POST',
    'content' => json_encode($data),
    'header' => implode(PHP_EOL,$header)
  ));
  $options = stream_context_create($options);
  $contents = file_get_contents($url, false, $options);
  return json_decode($contents,true);
}
