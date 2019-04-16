<?php
function getUser($id, $mode = "id") {
    global $userCache;
    if (isset($userCache[$mode], $userCache[$mode][$id])) {
        return $userCache[$mode][$id];
    }
    if (!$id) {
        return false;
    }
    $mysqli = db_start();
    if ($mode === "acct") {
        $stmt = $mysqli->prepare("SELECT * FROM `users` WHERE LOWER(acct) = LOWER(?);");
    } elseif ($mode === "twitter_id") {
        $stmt = $mysqli->prepare("SELECT * FROM `users` WHERE twitter_id = ?;");
    } elseif ($mode === "opener_token") {
        $stmt = $mysqli->prepare("SELECT * FROM `users` WHERE opener_token = ?;");
    } elseif ($mode === "broadcaster_id") {
        $stmt = $mysqli->prepare("SELECT * FROM `users` WHERE broadcaster_id = ?;");
    } else {
        $stmt = $mysqli->prepare("SELECT * FROM `users` WHERE id = ?;");
    }
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $row = db_fetch_all($stmt);
    $stmt->close();
    $mysqli->close();

    if (isset($row[0]["id"])) {
        $row[0]["misc"] = json_decode($row[0]["misc"], true);
        $row[0]["ngwords"] = empty($row[0]["ngwords"]) ? [] : json_decode($row[0]["ngwords"], true);
    }

    if (!isset($userCache[$mode])) {
        $userCache[$mode] = [];
    }
    $userCache[$mode][$id] = isset($row[0]["id"]) ? $row[0] : false;
    return $userCache[$mode][$id];
}

function getMe() {
    return isset($_SESSION["acct"]) ? getUser($_SESSION["acct"], "acct") : false;
}

function setUserLive($id, $user_id) {
    $mysqli = db_start();
    $stmt = $mysqli->prepare("UPDATE `users` SET live_current_id = ? WHERE id = ?;");
    $stmt->bind_param("ss", $id, $user_id);
    $stmt->execute();
    $stmt->close();
    $mysqli->close();
}

function setConfig($id, $misc, $donate_desc = false) {
    $misc = json_encode($misc, true);
    $mysqli = db_start();
    if ($donate_desc !== false) {
        $stmt = $mysqli->prepare("UPDATE `users` SET misc = ?, donation_desc = ? WHERE id = ?;");
        $stmt->bind_param("sss", $misc, $donate_desc, $id);
    } else {
        $stmt = $mysqli->prepare("UPDATE `users` SET misc = ? WHERE id = ?;");
        $stmt->bind_param("ss", $misc, $id);
    }
    $stmt->execute();
    $stmt->close();
    $mysqli->close();
}

function generateOpenerToken($id) {
    $hash = bin2hex(random_bytes(32));
    $mysqli = db_start();
    $stmt = $mysqli->prepare("UPDATE `users` SET opener_token = ? WHERE id = ?;");
    $stmt->bind_param("ss", $hash, $id);
    $stmt->execute();
    $err = $stmt->error;
    $stmt->close();
    $mysqli->close();
    return $err ? false : $hash;
}

function getMyLastLive($user_id) {
    $mysqli = db_start();
    $stmt = $mysqli->prepare("SELECT * FROM `live` WHERE user_id = ? ORDER BY id desc LIMIT 1;");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $row = db_fetch_all($stmt);
    $stmt->close();
    $mysqli->close();
    return isset($row[0]["id"]) ? $row[0] : false;
}

function setNgWords($words, $user_id) {
    $words = json_encode($words, true);
    $mysqli = db_start();
    $stmt = $mysqli->prepare("UPDATE `users` SET ngwords = ? WHERE id = ?;");
    $stmt->bind_param("ss", $words, $user_id);
    $stmt->execute();
    $err = $stmt->error;
    $stmt->close();
    $mysqli->close();

    return !$err;
}

function updateBroadcasterId($user_id, $new) {
    $new = s($new);
    $exist_bid = getUser($new, "broadcaster_id");
    if (preg_match('/([^A-Za-z0-9@\.]+)/', $new) || !empty($exist_bid)) {
        return false;
    }

    $mysqli = db_start();
    $stmt = $mysqli->prepare("UPDATE `users` SET broadcaster_id = ? WHERE id = ?;");
    $stmt->bind_param("ss", $new, $user_id);
    $stmt->execute();
    $err = $stmt->error;
    $stmt->close();
    $mysqli->close();
    return !$err;
}

function getAllLiveTime($user_id) {
    $mysqli = db_start();
    $stmt = $mysqli->prepare("SELECT * FROM `live` WHERE user_id = ? AND is_live = 0 AND is_started = 1;");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $row = db_fetch_all($stmt);
    $stmt->close();
    $mysqli->close();
    if (!isset($row[0]["id"])) {
        return [0];
    }

    $times = [];
    foreach ($row as $item) {
        $times[] = strtotime($item["ended_at"]) - strtotime($item["created_at"]);
    }

    array_multisort($times, SORT_DESC, SORT_NUMERIC);
    return $times;
}

function user4Pub($u) {
    return [
        "id" => $u["id"],
        "name" => $u["name"],
        "acct" => $u["acct"],
        "created_at" => $u["created_at"],
        "broadcaster_id" => !!$u["broadcaster_id"],
        "live_current_id" => $u["live_current_id"],
        "avatar_url" => $u["misc"]["avatar"],
        "header_url" => $u["misc"]["header"],
        "url" => !empty($u["misc"]["url"]) ? $u["misc"]["url"] : ""
    ];
}
