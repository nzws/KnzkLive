<?php
function getUser($id, $mode = "") {
  global $userCache;
  if (isset($userCache[$id])) return $userCache[$id];
  if (!$id) return false;
  $mysqli = db_start();
  if ($mode === "acct") {
    $stmt = $mysqli->prepare("SELECT * FROM `users` WHERE acct = ?;");
  } else {
    $stmt = $mysqli->prepare("SELECT * FROM `users` WHERE id = ?;");
  }
  $stmt->bind_param("s", $id);
  $stmt->execute();
  $row = db_fetch_all($stmt);
  $stmt->close();
  $mysqli->close();

  if (isset($row[0]["id"])) {
    $row[0]["misc"] = json_decode($row[0]["misc"], true);
  }
  $userCache[$id] = isset($row[0]["id"]) ? $row[0] : false;
  return $userCache[$id];
}

function getMe() {
  return isset($_SESSION["acct"]) ? getUser($_SESSION["acct"], "acct") : false;
}

function setUserLive($id) {
  $mysqli = db_start();
  $stmt = $mysqli->prepare("UPDATE `users` SET liveNow = ? WHERE acct = ?;");
  $stmt->bind_param("ss", $id, $_SESSION["acct"]);
  $stmt->execute();
  $stmt->close();
  $mysqli->close();
}

function setConfig($id, $misc) {
  $misc = json_encode($misc, true);
  $mysqli = db_start();
  $stmt = $mysqli->prepare("UPDATE `users` SET misc = ? WHERE id = ?;");
  $stmt->bind_param("ss", $misc, $id);
  $stmt->execute();
  $stmt->close();
  $mysqli->close();
}

function getMyLastLive($user_id) {
  $mysqli = db_start();
  $stmt = $mysqli->prepare("SELECT * FROM `live` WHERE user_id = ? ORDER BY id desc LIMIT 1;");
  $stmt->bind_param("s", $user_id);
  $stmt->execute();
  $row = db_fetch_all($stmt);
  $stmt->close();
  $mysqli->close();
  return isset($row[0]["id"]) ? $row[0] : false;
}