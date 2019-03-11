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

function getEmojis($user_id, $type) {
  global $env;

  $mysqli = db_start();
  if ($type === "comment") {
    $stmt = $mysqli->prepare("SELECT * FROM `items` WHERE user_id = ? AND type = 'emoji' AND able_comment = 1;");
  } else {
    $stmt = $mysqli->prepare("SELECT * FROM `items` WHERE user_id = ? AND type = 'emoji' AND able_item = 1;");
  }
  $stmt->bind_param("s", $user_id);
  $stmt->execute();
  $row = db_fetch_all($stmt);
  $stmt->close();
  $mysqli->close();
  $d = [];
  if (isset($row[0]["id"])) {
    foreach ($row as $item) {
      $d[] = [
        "url" => $env["storage"]["root_url"] . $item["file_name"],
        "code" => $item["name"]
      ];
    }
  }

  return $d;
}
