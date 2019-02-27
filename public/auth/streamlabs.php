<?php
require_once("../../lib/bootloader.php");

$my = getMe();
if (!$my) exit("ERR: 先にログインしてください。");

if (isset($_GET["code"])) {
  $data = [
    "client_id" => $env["streamlabs"]["id"],
    "client_secret" => $env["streamlabs"]["secret"],
    "grant_type" => "authorization_code",
    "redirect_uri" => $env["streamlabs"]["redirect_uri"],
    "code" => $_GET["code"]
  ];

  $options = array('http' => array(
    'method' => 'POST',
    'content' => json_encode($data),
    'header' => implode(PHP_EOL,['Content-Type: application/json'])
  ));
  $options = stream_context_create($options);
  $contents = file_get_contents("https://streamlabs.com/api/v1.0/token", false, $options);
  $json = json_decode($contents,true);
  if (empty($json["access_token"])) exit("ERR: アクセストークンが受信できませんでした。");
  $token = $json["access_token"];

  $options = array('http' => array(
    'method' => 'GET',
    'header' => implode(PHP_EOL,['Content-Type: application/json'])
  ));
  $options = stream_context_create($options);
  $contents = file_get_contents("https://streamlabs.com/api/v1.0/socket/token?access_token=" . $token, false, $options);
  $json = json_decode($contents,true);
  if (empty($json["socket_token"])) exit("ERR: Socket APIトークンが受信できませんでした。");

  $my["misc"]["streamlabs_token"] = $json["socket_token"];
  setConfig($my["id"], $my["misc"]);

  header("Location: " . u("settings"));
} else {
  header("Location: https://www.streamlabs.com/api/v1.0/authorize?client_id={$env["streamlabs"]["id"]}&redirect_uri={$env["streamlabs"]["redirect_uri"]}&response_type=code&scope=donations.read+socket.token");
}
