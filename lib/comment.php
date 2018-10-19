<?php
function comment_post($content, $user_id, $live_id) {
  $user_id = s($user_id);
  $live_id = s($live_id);

  if (!$content || !$user_id || !$live_id) return "値が不足しています。";
  if (mb_strlen($content) > 500) return "制限に達しています。";

  $mysqli = db_start();
  $stmt = $mysqli->prepare("INSERT INTO `comment` (`id`, `user_id`, `content`, `created_at`, `live_id`, `is_deleted`) VALUES (NULL, ?, ?, CURRENT_TIMESTAMP, ?, 0);");
  $stmt->bind_param('sss', $user_id, $content, $live_id);
  $stmt->execute();
  $err = $stmt->error;
  $id = $mysqli->insert_id;
  $stmt->close();
  $mysqli->close();
  return $err ? "データベースエラー" : $id;
}

function comment_get($live_id) {
  $mysqli = db_start();
  $stmt = $mysqli->prepare("SELECT * FROM `comment` WHERE live_id = ? ORDER BY id desc LIMIT 30;");
  $stmt->bind_param("s", $live_id);
  $stmt->execute();
  $row = db_fetch_all($stmt);
  $stmt->close();
  $mysqli->close();
  return isset($row[0]["id"]) ? $row : false;
}