<?php
function comment_post($content, $user_id, $live_id, $is_html = false) {
  global $env;
  $content = $is_html ? $content : "<p>" . HTMLHelper($content) . "</p>";
  $user_id = s($user_id);
  $live_id = s($live_id);
  $my = getUser($user_id);

  if (!$content || !$user_id || !$live_id) return "値が不足しています。";
  if (mb_strlen($content) < 7 || mb_strlen($content) > 500) return "制限に達しています。";

  $mysqli = db_start();
  $stmt = $mysqli->prepare("INSERT INTO `comment` (`id`, `user_id`, `content`, `created_at`, `live_id`, `is_deleted`) VALUES (NULL, ?, ?, CURRENT_TIMESTAMP, ?, 0);");
  $stmt->bind_param('sss', $user_id, $content, $live_id);
  $stmt->execute();
  $err = $stmt->error;
  $id = $mysqli->insert_id;
  $stmt->close();
  $mysqli->close();

  if ($err) return "データベースエラー";

  comment_count_add($live_id);

  $data = [
    "id" => "knzklive_".$id,
    "live_id" => $live_id,
    "is_knzklive" => true,
    "account" => [
      "display_name" => $my["name"],
      "acct" => $my["acct"]." (local)",
      "username" => $my["acct"]." (local)",
      "avatar" => $my["misc"]["avatar"],
      "url" => $my["misc"]["user_url"]
    ],
    "content" => $content,
  ];

  $header = [
    'Content-Type: application/json'
  ];

  $options = array('http' => array(
    'method' => 'POST',
    'content' => json_encode($data),
    'header' => implode(PHP_EOL,$header)
  ));
  $options = stream_context_create($options);
  $contents = file_get_contents($env["websocket_url"]."/send_comment", false, $options);
  return $id;
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

function comment_count_add($live_id) {
  $mysqli = db_start();
  $stmt = $mysqli->prepare("UPDATE `live` SET `comment_count` = `comment_count` + 1 WHERE id = ?;");
  $stmt->bind_param("s", $live_id);
  $stmt->execute();
  $stmt->close();
  $mysqli->close();
}
