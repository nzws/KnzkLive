<?php
function getItems($user_id, $type) {
  $mysqli = db_start();
  $stmt = $mysqli->prepare("SELECT * FROM `items` WHERE user_id = ? AND type = ?;");
  $stmt->bind_param("ss", $user_id, $type);
  $stmt->execute();
  $row = db_fetch_all($stmt);
  $stmt->close();
  $mysqli->close();
  return isset($row[0]["id"]) ? $row : [];
}

function getItem($id) {
  $mysqli = db_start();
  $stmt = $mysqli->prepare("SELECT * FROM `items` WHERE id = ?;");
  $stmt->bind_param("s", $id);
  $stmt->execute();
  $row = db_fetch_all($stmt);
  $stmt->close();
  $mysqli->close();
  return isset($row[0]["id"]) ? $row[0] : false;
}
