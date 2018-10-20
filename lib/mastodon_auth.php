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