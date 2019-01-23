<?php
function getAbleSlot() {
    global $env;
    $mysqli = db_start();
    $stmt = $mysqli->prepare("SELECT * FROM `live_slot` WHERE is_testing = ? AND used < `max`;");
    $testing = $env["is_testing"] ? 1 : 0;
    $stmt->bind_param("s", $testing);
    $stmt->execute();
    $row = db_fetch_all($stmt);
    $stmt->close();
    $mysqli->close();
    if (isset($row[0]["id"])) {
        return $row[0]["id"];
    } else {
        return false;
    }
}

function setSlot($id, $mode) {
    $mysqli = db_start();
    if ($mode) {
        $stmt = $mysqli->prepare("UPDATE `live_slot` SET used = used + 1 WHERE id = ?;");
    } else {
        $stmt = $mysqli->prepare("UPDATE `live_slot` SET used = used - 1 WHERE id = ?;");
    }
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $stmt->close();
    $mysqli->close();
}

function getSlot($id) {
    $mysqli = db_start();
    $stmt = $mysqli->prepare("SELECT * FROM `live_slot` WHERE id = ?;");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $row = db_fetch_all($stmt);
    $stmt->close();
    $mysqli->close();
    return isset($row[0]["id"]) ? $row[0] : false;
}

function getLive($id) {
    $mysqli = db_start();
    $stmt = $mysqli->prepare("SELECT * FROM `live` WHERE id = ?;");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $row = db_fetch_all($stmt);
    $stmt->close();
    $mysqli->close();
    return isset($row[0]["id"]) ? $row[0] : false;
}

function getAllLive($notId = 0, $is_history = false) {
    $mysqli = db_start();
    if ($is_history) {
      $stmt = $mysqli->prepare("SELECT * FROM `live` WHERE privacy_mode = 1 AND is_started = 1 AND id != ? ORDER BY ended_at desc LIMIT 0, 12;");
    } else {
      $stmt = $mysqli->prepare("SELECT * FROM `live` WHERE privacy_mode = 1 AND (is_live = 1 OR is_live = 2) AND is_started = 1 AND id != ? ORDER BY viewers_count desc;");
    }
    $stmt->bind_param("s", $notId);
    $stmt->execute();
    $row = db_fetch_all($stmt);
    $stmt->close();
    $mysqli->close();
    return isset($row[0]["id"]) ? $row : false;
}

function setLiveStatus($id, $mode) {
    $mysqli = db_start();
    $stmt = $mysqli->prepare("UPDATE `live` SET is_live = ? WHERE id = ?;");
    $stmt->bind_param("ss", $mode, $id);
    $stmt->execute();
    $err = $stmt->error;
    $stmt->close();
    $mysqli->close();
    return !$err;
}

function setViewersCount($id, $add = false) {
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

  if ($add) {
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

function postLiveStart($live, $is_notification, $visibility) {
  global $env;
  $liveUser = getUser($live["user_id"]);
  $url = liveUrl($live["id"]);
  $tag = liveTag($live);
  $text = <<< EOF
#KnzkLive 配信開始:
{$live["name"]} by {$liveUser["name"]}
{$url}
コメントタグ: #{$tag}
EOF;
  if ($is_notification) $text .= "\n!kl_start";

  $data = [
    "status" => $text,
    "visibility" => $visibility
  ];

  $header = [
    'Authorization: Bearer '.$env["notification_token"],
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

function postWebHook($live) {
  $liveUser = getUser($live["user_id"]);
  if (empty($liveUser["misc"]["webhook_url"])) return false;

  $data = live4Pub($live);
  $data["account"] = user4Pub($liveUser);

  $header = [
    'Content-Type: application/json'
  ];

  $options = array('http' => array(
    'method' => 'POST',
    'content' => json_encode($data),
    'header' => implode(PHP_EOL,$header)
  ));
  $options = stream_context_create($options);
  return file_get_contents($liveUser["misc"]["webhook_url"], false, $options);
}

function live4Pub($live) {
  return [
    "name" => $live["name"],
    "description" => $live["description"],
    "created_at" => $live["created_at"],
    "ended_at" => $live["ended_at"],
    "live_status" => $live["is_live"],
    "viewers_count" => $live["viewers_count"],
    "viewers_max" => $live["viewers_max"],
    "viewers_max_concurrent" => $live["viewers_max_concurrent"],
    "is_started" => $live["is_started"],
    "point_count" => $live["point_count"]
  ];
}
