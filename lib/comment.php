<?php
function comment_post($content, $user_id, $live_id, $is_html = false) {
    global $env;
    $content = $is_html ? $content : "<p>" . HTMLHelper($content, ["ignore_nl" => true]) . "</p>";
    $user_id = s($user_id);
    $live_id = s($live_id);
    $my = getUser($user_id);

    if (!$content || !$user_id || !$live_id) {
        return "値が不足しています。";
    }
    if (mb_strlen($content) < 7 || mb_strlen($content) > 500) {
        return "制限に達しています。";
    }

    $mysqli = db_start();
    $stmt = $mysqli->prepare("INSERT INTO `comment` (`id`, `user_id`, `content`, `created_at`, `live_id`, `is_deleted`) VALUES (NULL, ?, ?, CURRENT_TIMESTAMP, ?, 0);");
    $stmt->bind_param('sss', $user_id, $content, $live_id);
    $stmt->execute();
    $err = $stmt->error;
    $id = $mysqli->insert_id;
    $stmt->close();
    $mysqli->close();

    if ($err) {
        return "データベースエラー";
    }

    if (!$is_html) {
        comment_count_add($live_id, $my["id"]);
    }
    // is_html が true なら大体システムメッセージなのでカウントする必要が無い

    $data = [
        "id" => "knzklive_" . $id,
        "live_id" => $live_id,
        "is_knzklive" => true,
        "account" => [
            "display_name" => $my["name"],
            "acct" => $my["acct"] . " (local)",
            "username" => $my["acct"],
            "avatar" => $my["misc"]["avatar"],
            "url" => $my["misc"]["user_url"]
        ],
        "content" => $content,
    ];

    $header = [
        'Content-Type: application/json'
    ];

    $options = ['http' => [
        'method' => 'POST',
        'content' => json_encode($data),
        'header' => implode(PHP_EOL, $header)
    ]];
    $options = stream_context_create($options);
    $contents = file_get_contents($env["websocket_url"] . "/send_comment", false, $options);
    return $id;
}

function comment_get($live_id) {
    $mysqli = db_start();
    $stmt = $mysqli->prepare("SELECT * FROM `comment` WHERE live_id = ? AND is_deleted = 0 ORDER BY id desc LIMIT 30;");
    $stmt->bind_param("s", $live_id);
    $stmt->execute();
    $row = db_fetch_all($stmt);
    $stmt->close();
    $mysqli->close();
    return isset($row[0]["id"]) ? $row : false;
}

function comment_count_add($live_id, $user_id = null) {
    $mysqli = db_start();
    $stmt = $mysqli->prepare("UPDATE `live` SET `comment_count` = `comment_count` + 1 WHERE id = ?;");
    $stmt->bind_param("s", $live_id);
    $stmt->execute();
    $stmt->close();
    $mysqli->close();

    if (!empty($user_id)) {
        $mysqli = db_start();
        $stmt = $mysqli->prepare("UPDATE `users` SET `point_count_today_toot` = `point_count_today_toot` + 2 WHERE id = ?;");
        $stmt->bind_param("s", $user_id);
        $stmt->execute();
        $stmt->close();
        $mysqli->close();
    }
}

function comment_delete($user_id, $live_id, $comment_id, $is_knzklive = false) {
    global $env;

    $mysqli = db_start();
    if ($is_knzklive) {
        $stmt = $mysqli->prepare("UPDATE `comment` SET is_deleted = ? WHERE id = ?;");
        $stmt->bind_param('ss', $user_id, $comment_id);
    } else {
        $stmt = $mysqli->prepare("INSERT INTO `comment_delete` (`id`, `live_id`, `created_by`) VALUES (?, ?, ?);");
        $stmt->bind_param('sss', $comment_id, $live_id, $user_id);
    }
    $stmt->execute();
    $err = $stmt->error;
    $stmt->close();
    $mysqli->close();

    if ($err) {
        return "データベースエラー";
    }

    $options = ['http' => [
        'method' => 'POST',
        'content' => json_encode([
            "live_id" => $live_id,
            "delete_id" => ($is_knzklive ? "knzklive_" : "") . $comment_id
        ]),
        'header' => implode(PHP_EOL, [
            'Content-Type: application/json'
        ])
    ]];
    $options = stream_context_create($options);
    $contents = file_get_contents($env["websocket_url"] . "/delete_comment", false, $options);
    return true;
}

function get_comment_deleted_list($live_id) {
    $mysqli = db_start();
    $stmt = $mysqli->prepare("SELECT * FROM `comment_delete` WHERE live_id = ?;");
    $stmt->bind_param("s", $live_id);
    $stmt->execute();
    $row = db_fetch_all($stmt);
    $stmt->close();
    $mysqli->close();

    $ids = [];
    if ($row) {
        foreach ($row as $item) {
            $ids[] = $item["id"];
        }
    }

    return $ids;
}
