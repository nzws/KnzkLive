<?php
function add_point($user_id, $point, $type, $data) {
  $user = getUser($user_id);
  if (($point + $user["point_count"]) > 10000) {
    $point = 10000 - $user["point_count"];
    $data .= " (上限超過)";
  }
  if (($point + $user["point_count"]) < 0) {
    return false;
  }

  $point = intval($point);
  $mysqli = db_start();
  $stmt = $mysqli->prepare("INSERT INTO `point_log` (`user_id`, `type`, `data`, `point`) VALUES (?, ?, ?, ?);");
  $stmt->bind_param('ssss', $user_id, $type, $data, $point);
  $stmt->execute();
  $err = $stmt->error;
  $stmt->close();
  $mysqli->close();

  if ($err) return false;

  $mysqli = db_start();
  $stmt = $mysqli->prepare("UPDATE `users` SET `point_count` = `point_count` + ? WHERE `id` = ?");
  $stmt->bind_param("ss", $point, $user_id);
  $stmt->execute();
  $err = $stmt->error;
  $stmt->close();
  $mysqli->close();
  return !$err;
}

function create_ticket($user_id, $point, $comment) {
  $hash = bin2hex(openssl_random_pseudo_bytes(32));
  $hash = mb_substr($hash, 0, 10);
  $point = intval($point);
  $mysqli = db_start();
  $stmt = $mysqli->prepare("INSERT INTO `point_ticket` (`id`, `point`, `user_id`, `comment`) VALUES (?, ?, ?, ?);");
  $stmt->bind_param('ssss', $hash, $point, $user_id, $comment);
  $stmt->execute();
  $err = $stmt->error;
  $stmt->close();
  $mysqli->close();

  return !$err ? $hash : false;
}

function get_ticket($id) {
  $mysqli = db_start();
  $stmt = $mysqli->prepare("SELECT * FROM `point_ticket` WHERE `id` = ? AND `used_by` IS NULL;");
  $stmt->bind_param("s", $id);
  $stmt->execute();
  $row = db_fetch_all($stmt);
  $stmt->close();
  $mysqli->close();

  return isset($row[0]) ? $row[0] : false;
}

function use_ticket($user_id, $ticket_id) {
  $t = get_ticket($ticket_id);
  if ($t["id"] !== $ticket_id) return false;
  $n = add_point($user_id, $t["point"], "user", "チケット使用 コメント: " . s($t["comment"]));
  if (!$n) return false;

  $mysqli = db_start();
  $stmt = $mysqli->prepare("UPDATE `point_ticket` SET used_by = ? WHERE `id` = ?");
  $stmt->bind_param("ss", $user_id, $ticket_id);
  $stmt->execute();
  $err = $stmt->error;
  $stmt->close();
  $mysqli->close();
  return !$err;
}

function get_point_log($user_id, $type = "stat", $page = 0) {
  global $point_log_cache;
  if (!empty($point_log_cache[$type][$user_id])) return $point_log_cache[$type][$user_id];

  $mysqli = db_start();
  if ($type === "hist") {
    $max_id = 10 * intval($page);
    $stmt = $mysqli->prepare("SELECT * FROM `point_log` WHERE `user_id` = ? ORDER BY id desc LIMIT ?, 10;");
    $stmt->bind_param("ss", $user_id, $max_id);
  } else {
    $ts = date('Ym');
    $stmt = $mysqli->prepare("SELECT * FROM `point_log` WHERE `user_id` = ? AND DATE_FORMAT(created_at, '%Y%m') = ? ORDER BY id desc;");
    $stmt->bind_param("ss", $user_id, $ts);
  }
  $stmt->execute();
  $row = db_fetch_all($stmt);
  $stmt->close();
  $mysqli->close();

  $point_log_cache[$type][$user_id] = $row;
  return isset($row[0]["id"]) ? $row : [];
}

function get_point_log_stat($user_id, $type, $day_type) {
  $l = get_point_log($user_id);
  $point = 0;
  foreach ($l as $item) {
    if ($item["type"] === $type && $item["point"] > 0) {
      if (
      ($day_type === "today" && date('Ymd') === date('Ymd', strtotime($item["created_at"]))) ||
      ($day_type === "yesterday" && date('Ymd', strtotime("-1 day")) === date('Ymd',  strtotime($item["created_at"]))) ||
      ($day_type === "month" && date('Ym') === date('Ym',  strtotime($item["created_at"])))
      ) {
        $point += $item["point"];
      }
    }
  }
  return $point;
}

function check_point_true($now_have_point, $use_point) {
  return !(intval($use_point) > $now_have_point || !$use_point || intval($use_point) <= 0 || !is_numeric($use_point));
}

function point_count_add($live_id, $point) {
  $mysqli = db_start();
  $stmt = $mysqli->prepare("UPDATE `live` SET `point_count` = `point_count` + ? WHERE id = ?;");
  $stmt->bind_param("ss", $point, $live_id);
  $stmt->execute();
  $stmt->close();
  $mysqli->close();
}
