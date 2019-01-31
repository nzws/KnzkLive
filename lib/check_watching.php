<?php
function updateWatcher($ip, $watch_id) {
  $live = getLive($watch_id);
  if ($live["is_live"] != 0) {
    $mysqli = db_start();
    $stmt = $mysqli->prepare("SELECT * FROM `users_watching` WHERE `ip` = ? AND `watch_id` = ?");
    $stmt->bind_param('ss', $ip, $live["id"]);
    $stmt->execute();
    $row = db_fetch_all($stmt);
    $stmt->close();
    $mysqli->close();

    if (isset($row[0]["ip"])) { //update
      $mysqli = db_start();
      $stmt = $mysqli->prepare("UPDATE `users_watching` SET `updated_at` = CURRENT_TIMESTAMP, `watching_now` = 1 WHERE `ip` = ? AND `watch_id` = ?;");
      $stmt->bind_param('ss', $ip, $live["id"]);
      $stmt->execute();
      $stmt->close();
      $mysqli->close();
      if ($row[0]["watching_now"] === 0) //rejoin
        setViewersCount($live["id"], true, false);
    } else { //join
      $mysqli = db_start();
      $stmt = $mysqli->prepare("INSERT INTO `users_watching` (ip, watch_id, updated_at) VALUES (?, ?, CURRENT_TIMESTAMP);");
      $stmt->bind_param('ss', $ip, $live["id"]);
      $stmt->execute();
      $stmt->close();
      $mysqli->close();

      setViewersCount($live["id"], true); //plus
      if ($live["viewers_max_concurrent"] < getLive($live["id"])["viewers_count"]) {
        $mysqli = db_start();
        $stmt = $mysqli->prepare("UPDATE `live` SET viewers_max_concurrent = viewers_count WHERE id = ?;");
        $stmt->bind_param("s", $live["id"]);
        $stmt->execute();
        $stmt->close();
        $mysqli->close();
      }
    }
  }
}

function leaveWatcher($ip, $watch_id) {
  $mysqli = db_start();
  $stmt = $mysqli->prepare("UPDATE `users_watching` SET `watching_now` = 0 WHERE `ip` = ? AND `watch_id` = ?;");
  $stmt->bind_param('ss', $ip, $watch_id);
  $stmt->execute();
  $stmt->close();
  $mysqli->close();
  setViewersCount($watch_id); //minus
  return true;
}

function setViewersCount($id, $add = false, $is_unique = true) {
  $mysqli = db_start();
  if ($add) {
    $stmt = $mysqli->prepare("UPDATE `live` SET viewers_count = viewers_count + 1 WHERE id = ? AND is_live != 0;");
  } else {
    $stmt = $mysqli->prepare("UPDATE `live` SET viewers_count = viewers_count - 1 WHERE id = ?;");
  }
  $stmt->bind_param("s", $id);
  $stmt->execute();
  $err = $stmt->error;
  $stmt->close();
  $mysqli->close();

  if ($add && $is_unique) {
    $mysqli = db_start();
    $stmt = $mysqli->prepare("UPDATE `live` SET viewers_max = viewers_max + 1 WHERE id = ? AND is_live != 0;");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $err = $stmt->error;
    $stmt->close();
    $mysqli->close();
  }

  return !$err;
}

function checkLeftUsers() {
  $mysqli = db_start();
  // 1分以上アップデートされていないユーザーは退出済みとみなす
  $stmt = $mysqli->prepare("SELECT * FROM `users_watching` WHERE `updated_at` < ( NOW() - INTERVAL 1 MINUTE ) AND watching_now = 1");
  $stmt->execute();
  $row = db_fetch_all($stmt);
  $stmt->close();
  $mysqli->close();
  if (isset($row[0])) {
    $i = 0;
    while (isset($row[$i])) {
      leaveWatcher(s($row[$i]["ip"]), s($row[$i]["watch_id"]));
      $i++;
    }
  }
}

function deleteAllWatcher($live_id) {
  $mysqli = db_start();
  $stmt = $mysqli->prepare("DELETE FROM `users_watching` WHERE `watch_id` = ?;");
  $stmt->bind_param("s", $live_id);
  $stmt->execute();
  $stmt->close();
  $mysqli->close();
}
