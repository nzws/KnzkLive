<?php
function createVote($live_id, $title, $data, $hashtag, $user_id) {
    global $env;
    if (empty($title) || empty($data[0]) || empty($data[1])) {
        return false;
    }

    $title = s($title);
    $data[0] = s($data[0]);
    $data[1] = s($data[1]);
    $data[2] = s($data[2]);
    $data[3] = s($data[3]);

    $mysqli = db_start();
    $stmt = $mysqli->prepare("INSERT INTO `prop_vote` (`live_id`, `title`, `v1`, `v2`, `v3`, `v4`) VALUES (?, ?, ?, ?, ?, ?);");
    $stmt->bind_param('ssssss', $live_id, $title, $data[0], $data[1], $data[2], $data[3]);
    $stmt->execute();
    $err = $stmt->error;
    $stmt->close();
    $mysqli->close();

    $d = [
        "type" => "vote_start",
        "live_id" => $live_id,
        "title" => $title,
        "vote" => $data
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

    $t = implode(" / ", $data);

    $text = <<< EOF
#KnzkLive 投票を作成しました！ #{$hashtag}
「{$title}」
{$t}

投票: https://live.knzk.me/watch{$live_id}
EOF;

    if ($_SESSION["prop_vote_is_post"]) {
        toot($text);
    } else {
        comment_post("<p class='vote_alert'>" . HTMLHelper($text) . "</p>", $user_id, $live_id, true);
    }

    return empty($err);
}

function loadVote($live_id) {
    $mysqli = db_start();
    $stmt = $mysqli->prepare("SELECT * FROM `prop_vote` WHERE `live_id` = ? AND `is_ended` = 0;");
    $stmt->bind_param("s", $live_id);
    $stmt->execute();
    $row = db_fetch_all($stmt);
    $stmt->close();
    $mysqli->close();
    return isset($row[0]["id"]) ? $row[0] : null;
}

function endVote($live_id, $hashtag, $user_id) {
    global $env;

    $v = loadVote($live_id);
    $res = <<< EOF
#KnzkLive 投票結果 #{$hashtag}
「{$v["title"]}」

{$v["v1"]}: {$v["v1_count"]}票
{$v["v2"]}: {$v["v2_count"]}票
EOF;
    if (!empty($v["v3"])) {
        $res .= "\n{$v["v3"]}: {$v["v3_count"]}票";
    }
    if (!empty($v["v4"])) {
        $res .= "\n{$v["v4"]}: {$v["v4_count"]}票";
    }
    if ($_SESSION["prop_vote_is_post"]) {
        toot($res);
    } else {
        comment_post("<p class='vote_alert'>" . HTMLHelper($res) . "</p>", $user_id, $live_id, true);
    }

    $mysqli = db_start();
    $stmt = $mysqli->prepare("UPDATE `prop_vote` SET `is_ended` = 1 WHERE `live_id` = ? AND `is_ended` = 0;");
    $stmt->bind_param("s", $live_id);
    $stmt->execute();
    $err = $stmt->error;
    $stmt->close();
    $mysqli->close();

    $data = [
        "type" => "vote_end",
        "live_id" => $live_id,
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
    $contents = file_get_contents($env["websocket_url"] . "/send_prop", false, $options);

    return empty($err);
}

function addVote($live_id, $type) {
    if (!empty($_SESSION["prop_vote_" . $live_id])) {
        return false;
    }
    $mysqli = db_start();
    $type = (int) $mysqli->real_escape_string($type);
    $stmt = $mysqli->prepare("UPDATE `prop_vote` SET `v{$type}_count` = `v{$type}_count` + 1 WHERE `live_id` = ? AND `is_ended` = 0;");
    $stmt->bind_param("s", $live_id);
    $stmt->execute();
    $err = $stmt->error;
    $stmt->close();
    $mysqli->close();
    $_SESSION["prop_vote_" . $live_id] = true;
    return empty($err);
}

function resetVoteHist($live_id) {
    if (empty(loadVote($live_id))) {
        $_SESSION["prop_vote_" . $live_id] = null;
    }
}
