<?php
function getMastodonAuth($domain) {
  $domain = mb_strtolower($domain, 'UTF-8');

  $mysqli = db_start();
  $stmt = $mysqli->prepare("SELECT * FROM `mastodon_auth` WHERE domain = ?;");
  $stmt->bind_param("s", $domain);
  $stmt->execute();
  $row = db_fetch_all($stmt);
  $stmt->close();
  $mysqli->close();
  return isset($row[0]["domain"]) ? $row[0] : false;
}

function setMastodonAuth($domain, $id, $key) {
  $domain = mb_strtolower($domain, 'UTF-8');
  $id = s($id);
  $key = s($key);

  $mysqli = db_start();
  $stmt = $mysqli->prepare("INSERT INTO `mastodon_auth` (`domain`, `client_id`, `client_secret`) VALUES (?, ?, ?);");
  $stmt->bind_param('sss', $domain, $id, $key);
  $stmt->execute();
  $stmt->close();
  $mysqli->close();
}

function toot($text, $visibility = "public") {
  global $env;

  $data = [
    "status" => $text,
    "visibility" => $visibility
  ];

  $header = [
    'Authorization: Bearer '.$_SESSION["token"],
    'Content-Type: application/json'
  ];

  $options = array('http' => array(
    'method' => 'POST',
    'content' => json_encode($data),
    'header' => implode(PHP_EOL,$header)
  ));
  $options = stream_context_create($options);
  $contents = file_get_contents("https://".$env["masto_login"]["domain"]."/api/v1/statuses", false, $options);
}
