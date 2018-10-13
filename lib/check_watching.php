<?php
function updateWatcher($ip, $watch_id) {
  $live = getLive($watch_id);
  if ($live["is_live"] != 0) {
    $mysqli = db_start();
    $stmt = $mysqli->prepare("SELECT * FROM `users_watching` WHERE `ip` = ?");
    $stmt->bind_param('s', $ip);
    $stmt->execute();
    $row = db_fetch_all($stmt);
    $stmt->close();
    $mysqli->close();

    if (isset($row[0]["ip"])) { //update
      if ($row[0]["watch_id"] == $live["id"]) {
        $mysqli = db_start();
        $stmt = $mysqli->prepare("UPDATE `users_watching` SET `watch_id` = ?, `updated_at` = CURRENT_TIMESTAMP WHERE `ip` = ?;");
        $stmt->bind_param('ss', $live["id"], $ip);
        $stmt->execute();
        $stmt->close();
        $mysqli->close();
      } else {
        leaveWatcher($ip, $row[0]["watch_id"]);
        updateWatcher($ip, $live["id"]);
      }
    } else { //join
      $mysqli = db_start();
      $stmt = $mysqli->prepare("INSERT INTO `users_watching` (ip, watch_id, updated_at) VALUES (?, ?, CURRENT_TIMESTAMP);");
      $stmt->bind_param('ss', $ip, $live["id"]);
      $stmt->execute();
      $stmt->close();
      $mysqli->close();

      setViewersCount($live["id"], true);
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
  $stmt = $mysqli->prepare("DELETE FROM `users_watching` WHERE `ip` = ?;");
  $stmt->bind_param('s', $ip);
  $stmt->execute();
  $stmt->close();
  $mysqli->close();
  setViewersCount($watch_id);
  return true;
}

function checkLeftUsers() {
  $mysqli = db_start();
  // 1分以上アップデートされていないユーザーは退出済みとみなす
  $stmt = $mysqli->prepare("SELECT * FROM `users_watching` WHERE `updated_at` < ( NOW() - INTERVAL 1 MINUTE )");
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