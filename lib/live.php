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
    if (isset($row[0]["id"])) {
        $row[0]["misc"] = json_decode($row[0]["misc"], true);
    }

    return isset($row[0]["id"]) ? $row[0] : false;
}

function setLiveConfig($live_id, $misc) {
    $misc = json_encode($misc, true);
    $mysqli = db_start();
    $stmt = $mysqli->prepare("UPDATE `live` SET misc = ? WHERE id = ?;");
    $stmt->bind_param("ss", $misc, $live_id);
    $stmt->execute();
    $err = $stmt->error;
    $stmt->close();
    $mysqli->close();
    return !$err;
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

function getLastLives() {
    $mysqli = db_start();
    $stmt = $mysqli->prepare("SELECT * FROM live WHERE id in (SELECT max(id) FROM live WHERE privacy_mode = 1 AND is_started = 1 GROUP BY user_id ORDER BY id DESC) ORDER BY id DESC;");
    $stmt->execute();
    $row = db_fetch_all($stmt);
    $stmt->close();
    $mysqli->close();
    return isset($row[0]["id"]) ? $row : false;
}

function getUserLives($user_id) {
    $mysqli = db_start();
    $stmt = $mysqli->prepare("SELECT * FROM `live` WHERE privacy_mode = 1 AND is_started = 1 AND user_id = ? ORDER BY ended_at desc LIMIT 0, 30;");
    $stmt->bind_param("s", $user_id);
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

    update_realtime_config("update_status", [
        "status" => $mode
    ], $id);

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
    if ($is_notification) {
        $text .= "\n!kl_start";
    }

    $data = [
        "status" => $text,
        "visibility" => $visibility
    ];

    $header = [
        'Authorization: Bearer ' . $env["notification_token"],
        'Content-Type: application/json'
    ];

    $options = ['http' => [
        'method' => 'POST',
        'content' => json_encode($data),
        'header' => implode(PHP_EOL, $header)
    ]];
    $options = stream_context_create($options);
    $contents = file_get_contents("https://" . $env["masto_login"]["domain"] . "/api/v1/statuses", false, $options);
}

function postWebHook($live) {
    $liveUser = getUser($live["user_id"]);
    if (empty($liveUser["misc"]["webhook_url"])) {
        return false;
    }

    $data = live4Pub($live);
    $data["account"] = user4Pub($liveUser);

    $header = [
        'Content-Type: application/json'
    ];

    $options = ['http' => [
        'method' => 'POST',
        'content' => json_encode($data),
        'header' => implode(PHP_EOL, $header)
    ]];
    $options = stream_context_create($options);
    return file_get_contents($liveUser["misc"]["webhook_url"], false, $options);
}

function end_live($live_id) {
    $live = getLive($live_id);
    if (!$live) {
        return false;
    }
    $my = getUser($live["user_id"]);

    if (setLiveStatus($live["id"], 0)) {
        setSlot($live["slot_id"], 0);
        setUserLive(0, $my["id"]);

        if ($live["is_live"] === 2) {
            disconnectClient($live["id"]);
        }
        foreach ($live["misc"]["collabo"] as $collaboId => $item) {
            setSlot($item["slot"], 0);
            if ($item["status"] === 2) {
                disconnectClient($live["id"], $collaboId);
            }
        }

        if (isset($my["misc"]["viewers_max_concurrent"])) {
            if ($live["viewers_max_concurrent"] > $my["misc"]["viewers_max_concurrent"]) {
                $my["misc"]["viewers_max_concurrent"] = $live["viewers_max_concurrent"];
            }
        } else {
            $my["misc"]["viewers_max"] = 0;
            $my["misc"]["viewers_max_concurrent"] = $live["viewers_max_concurrent"];
        }

        if (isset($my["misc"]["viewers_count_max"])) {
            if ($live["viewers_max"] > $my["misc"]["viewers_count_max"]) {
                $my["misc"]["viewers_count_max"] = $live["viewers_max"];
            }
        } else {
            $my["misc"]["viewers_count_max"] = $live["viewers_max"];
        }

        if (isset($my["misc"]["comment_count_max"])) {
            if ($live["comment_count"] > $my["misc"]["comment_count_max"]) {
                $my["misc"]["comment_count_max"] = $live["comment_count"];
            }
        } else {
            $my["misc"]["comment_count_all"] = 0;
            $my["misc"]["comment_count_max"] = $live["comment_count"];
        }

        if (isset($my["misc"]["point_count_max"])) {
            if ($live["point_count"] > $my["misc"]["point_count_max"]) {
                $my["misc"]["point_count_max"] = $live["point_count"];
            }
        } else {
            $my["misc"]["point_count_all"] = 0;
            $my["misc"]["point_count_max"] = $live["point_count"];
        }

        $time = time() - strtotime($live["created_at"]);
        if ($time < 0) {
            $time = 0;
        }
        if (isset($my["misc"]["time_max"])) {
            if ($time > $my["misc"]["time_max"]) {
                $my["misc"]["time_max"] = $time;
            }
        } else {
            $my["misc"]["time_all"] = 0;
            $my["misc"]["time_max"] = $time;
        }

        $my["misc"]["viewers_max"] += $live["viewers_max"];
        $my["misc"]["comment_count_all"] += $live["comment_count"];
        $my["misc"]["point_count_all"] += $live["point_count"];
        $my["misc"]["time_all"] += $time;

        setConfig($my["id"], $my["misc"]);
        deleteAllWatcher($live["id"]);

        $mysqli = db_start();
        $stmt = $mysqli->prepare("UPDATE `live` SET ended_at = CURRENT_TIMESTAMP, created_at = created_at WHERE id = ?;");
        $stmt->bind_param("s", $live["id"]);
        $stmt->execute();
        $stmt->close();
        $mysqli->close();

        $mysqli = db_start();
        $stmt = $mysqli->prepare("DELETE FROM `users_blocking` WHERE `live_user_id` = ? AND `is_permanent` = 0;");
        $stmt->bind_param("s", $my["id"]);
        $stmt->execute();
        $stmt->close();
        $mysqli->close();

        node_update_conf("del", "hashtag", liveTag($live), $live["id"], $my["id"]);
        $get_point = intval($live["point_count"] * 0.5);
        if ($get_point > 0) {
            add_point($my["id"], $get_point, "live", "配信ID:" . $live["id"] . " のポイント還元 (50%)");
        }
        return true;
    }
    return false;
}

function update_realtime_config($mode, $result, $live_id) {
    global $env;
    $d = [
        "type" => "change_config",
        "mode" => $mode,
        "result" => $result,
        "live_id" => $live_id,
    ];

    $header = [
        'Content-Type: application/json'
    ];

    $options = ['http' => [
        'method' => 'POST',
        'content' => json_encode($d),
        'header' => implode(PHP_EOL, $header)
    ]];
    $options = stream_context_create($options);
    $contents = file_get_contents($env["websocket_url"] . "/send_prop", false, $options);
}

function blocking_user($live_user_id, $ip = null, $user_acct = null) {
    $mysqli = db_start();
    $stmt = $mysqli->prepare("SELECT * FROM `users_blocking` WHERE live_user_id = ? AND (target_user_acct = ? OR target_user_acct IN (select acct from `users` WHERE ip = ?));");
    $stmt->bind_param("sss", $live_user_id, $user_acct, $ip);
    $stmt->execute();
    $row = db_fetch_all($stmt);
    $stmt->close();
    $mysqli->close();
    return isset($row[0]) ? $row[0] : null;
}

function get_all_blocking_user($live_user_id) {
    $mysqli = db_start();
    $stmt = $mysqli->prepare("SELECT * FROM `users_blocking` WHERE live_user_id = ?;");
    $stmt->bind_param("s", $live_user_id);
    $stmt->execute();
    $row = db_fetch_all($stmt);
    $stmt->close();
    $mysqli->close();
    return isset($row[0]) ? $row : [];
}

function disconnectClient($live_id, $collabo_id = null) {
    global $env;
    $live = getLive($live_id);
    if (!empty($collabo_id)) {
        $slot = getSlot($live["misc"]["collabo"][$collabo_id]["slot"]);
        $stream = $live["id"] . "stream" . $collabo_id . "collabo";
    } else {
        $slot = getSlot($live["slot_id"]);
        $stream = $live["id"] . "stream";
    }

    $d = [
        "authorization" => $env["publish_auth"],
        "live_stream" => $stream,
    ];

    $options = ['http' => [
        'method' => 'POST',
        'content' => json_encode($d),
        'header' => implode(PHP_EOL, ['Content-Type: application/json'])
    ]];
    $options = stream_context_create($options);
    $contents = file_get_contents("http://" . $slot["server_ip"] . "/api/knzk/stop", false, $options);
}

function live4Pub($live) {
    return [
        "id" => $live["id"],
        "name" => $live["name"],
        "description" => $live["description"],
        "created_at" => $live["created_at"],
        "ended_at" => $live["ended_at"],
        "live_status" => $live["is_live"],
        "viewers_count" => $live["viewers_count"],
        "viewers_max" => $live["viewers_max"],
        "viewers_max_concurrent" => $live["viewers_max_concurrent"],
        "is_started" => $live["is_started"],
        "point_count" => $live["point_count"],
        "hashtag" => liveTag($live),
        "is_knzklive" => true
    ];
}
