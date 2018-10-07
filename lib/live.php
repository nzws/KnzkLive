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

function getAllLive($notId = 0) {
    $mysqli = db_start();
    $stmt = $mysqli->prepare("SELECT * FROM `live` WHERE privacy_mode = 1 AND (is_live = 1 OR is_live = 2) AND id != ? ORDER BY viewers_count desc;");
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
    $stmt = $mysqli->prepare("UPDATE `live` SET viewers_count = viewers_count + 1 WHERE id = ?;");
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
    $stmt = $mysqli->prepare("UPDATE `live` SET viewers_max = viewers_max + 1 WHERE id = ?;");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $err = $stmt->error;
    $stmt->close();
    $mysqli->close();
  }

  return !$err;
}