<?php
function updateWatcher($ip, $watch_id, $user_id = null)
{
    $live = getLive($watch_id);
    if ($live["is_live"] != 0) {
        $mysqli = db_start();
        $stmt = $mysqli->prepare("SELECT * FROM `users_watching` WHERE `ip` = ? AND `watch_id` = ?");
        $stmt->bind_param('ss', $ip, $live["id"]);
        $stmt->execute();
        $row = db_fetch_all($stmt);
        $stmt->close();
        $mysqli->close();

        if (isset($row[0]["ip"])) { //update
            $mysqli = db_start();
            if ($row[0]["watching_now"] === 0) { // rejoin
                $stmt = $mysqli->prepare("UPDATE `users_watching` SET `created_at` = CURRENT_TIMESTAMP, `updated_at` = CURRENT_TIMESTAMP, `watching_now` = 1, `user_id` = ? WHERE `ip` = ? AND `watch_id` = ?;");
            } else {
                $stmt = $mysqli->prepare("UPDATE `users_watching` SET `updated_at` = CURRENT_TIMESTAMP, `watching_now` = 1, `user_id` = ? WHERE `ip` = ? AND `watch_id` = ?;");
            }
            $stmt->bind_param('sss', $user_id, $ip, $live["id"]);
            $stmt->execute();
            $stmt->close();
            $mysqli->close();
            if ($row[0]["watching_now"] === 0) { //rejoin
                setViewersCount($live["id"], true, false);
            }
        } else { //join
            $mysqli = db_start();
            $stmt = $mysqli->prepare("INSERT INTO `users_watching` (ip, watch_id, updated_at, user_id) VALUES (?, ?, CURRENT_TIMESTAMP, ?);");
            $stmt->bind_param('sss', $ip, $live["id"], $user_id);
            $stmt->execute();
            $stmt->close();
            $mysqli->close();

            setViewersCount($live["id"], true); //plus
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

function leaveWatcher($ip, $watch_id)
{
    $mysqli = db_start();
    $stmt = $mysqli->prepare("UPDATE `users_watching` SET `watching_now` = 0 WHERE `ip` = ? AND `watch_id` = ?;");
    $stmt->bind_param('ss', $ip, $watch_id);
    $stmt->execute();
    $stmt->close();
    $mysqli->close();
    setViewersCount($watch_id); //minus

    $live = getLive($watch_id);
    $mysqli = db_start();
    $stmt = $mysqli->prepare("SELECT * FROM `users_watching` WHERE `ip` = ? AND `watch_id` = ?");
    $stmt->bind_param('ss', $ip, $watch_id);
    $stmt->execute();
    $row = db_fetch_all($stmt);
    $stmt->close();
    $mysqli->close();
    if (!empty($row[0]) && !empty($row[0]["user_id"]) && $live["user_id"] !== $row[0]["user_id"]) {
        addWatchingPoint($row[0]["user_id"], strtotime($row[0]["created_at"]), strtotime($row[0]["updated_at"]), $live["id"]);
    }

    return true;
}

function addWatchingPoint($user_id, $start, $end, $live_id)
{
    $point = intval(($end - $start) / 600);
    $point = $point * 10;
    if ($point > 0) {
        $n = add_point($user_id, $point, "live", "配信ID: " . $live_id . " の視聴特典");
    }
}

function setViewersCount($id, $add = false, $is_unique = true)
{
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

    if ($add && $is_unique) {
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

function checkLeftUsers()
{
    $mysqli = db_start();
    // 1分以上アップデートされていないユーザーは退出済みとみなす
    $stmt = $mysqli->prepare("SELECT * FROM `users_watching` WHERE `updated_at` < ( NOW() - INTERVAL 1 MINUTE ) AND watching_now = 1");
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

function deleteAllWatcher($live_id)
{
    $live = getLive($live_id);
    $mysqli = db_start();
    $stmt = $mysqli->prepare("SELECT * FROM `users_watching` WHERE `watch_id` = ?");
    $stmt->bind_param('s', $live["id"]);
    $stmt->execute();
    $row = db_fetch_all($stmt);
    $stmt->close();
    $mysqli->close();
    if (!empty($row[0])) {
        foreach ($row as $item) {
            if (!empty($item["user_id"]) && $live["user_id"] !== $item["user_id"]) {
                addWatchingPoint($item["user_id"], strtotime($item["created_at"]), strtotime($item["updated_at"]), $live["id"]);
            }
        }
    }

    $mysqli = db_start();
    $stmt = $mysqli->prepare("DELETE FROM `users_watching` WHERE `watch_id` = ?;");
    $stmt->bind_param("s", $live_id);
    $stmt->execute();
    $stmt->close();
    $mysqli->close();
}
