<?php
function load($argv) {
    if ($argv[1] === "job:daily") {
        merge_toot_point();
    } elseif ($argv[1] === "management:rebuild_stat") {
        rebuild_stat();
    } elseif ($argv[1] === "management:add_broadcaster") {
        $my = getUser($argv[2]);
        echo updateBroadcasterId($my["id"], $my["acct"]);
    } elseif ($argv[1] === "job:stop_live") {
        $live = getLive($argv[2]);
        if ($live && $live["is_live"] === 1) {
            end_live($live["id"]);
            comment_post("<div class=\"alert alert-danger\">【システム】未プッシュの状態が15分間続いたため自動的に枠を終了しました。</div>", $live["user_id"], $live["id"], true);
        }
    } elseif ($argv[1] === "debug:add_collabo") {
        setCollaboLiveStatus($argv[3], $argv[2], $argv[4]);
    } elseif ($argv[1] === "job:tipknzk") {
        $my = getUser($argv[3], "acct");
        if (empty($my)) {
            exit("[Error] あなたのアカウントは存在しません。 https://live.knzk.me/ にログインしてください。");
        }
        if (intval($argv[2]) > $my["point_count"] || intval($argv[2]) <= 0 || !$argv[2] || !is_numeric($argv[2])) {
            exit("[Error] 残高が足りないかデータが不正です！ you don't have enough point!");
        }
        $u = getUser($argv[4], "acct");
        if ($u["id"] === $my["id"]) {
            exit("[Error] 自分自身には送信できません");
        }
        if ($u) {
            $n = add_point($my["id"], $argv[2] * -1, "user", $u["acct"] . "にプレゼント");
            if ($n) {
                $o = add_point($u["id"], $argv[2], "user", $my["acct"] . "からのプレゼント (TIPKnzk)");
                if ($o) {
                    echo "@{$u["acct"]} に {$argv[2]}KP 送りました！ you sent {$argv[2]}KP to @{$u["acct"]}!";
                } else {
                    exit("[Error] 例外エラー");
                }
            }
        } else {
            exit("[Error] 相手のKnzkPointアカウントが存在しません！　https://live.knzk.me/ にログインする必要があります！");
        }
    } elseif ($argv[1] === "job:donate") {
        if ($argv[3] === "testing") {
            comment_post("<div class=\"alert alert-warning\">DonationAlerts: テストOK</div>", getLive($argv[2])["user_id"], s($argv[2]), true);
        } else {
            add_donate(s($argv[2]), s($argv[3]), $argv[4], $argv[5]);
        }
    } else {
        disp_log("command {$argv[1]} is not found", 2);
    }

    exit("\n✨ Done!\n\n");
}

function disp_log($name, $type) {
    if ($type === 0) {
        echo "⏳ Starting: " . $name . "...\n";
    } elseif ($type === 1) {
        echo "✅ Succeed: " . $name . "!\n";
    } elseif ($type === 2) {
        exit("⁉️ Error occurred: " . $name . "\n");
    }
}

function merge_toot_point() {
    global $toot_get_limit;
    $name = "merge-toot-point";
    disp_log($name, 0);

    $limit = $toot_get_limit;
    $sql = "start transaction;";
    $sql .= "INSERT INTO `point_log` (`user_id`, `type`, `data`, `point`) SELECT id, 'toot', '', CASE WHEN point_count_today_toot > {$limit} THEN {$limit} ELSE point_count_today_toot END FROM `users` WHERE point_count_today_toot > 0;";
    $sql .= "UPDATE `users` SET `point_count` = `point_count` + CASE WHEN point_count_today_toot > {$limit} THEN {$limit} ELSE point_count_today_toot END, `point_count_today_toot` = 0 WHERE point_count_today_toot > 0;";
    $sql .= "UPDATE `users` SET `point_count` = 10000 WHERE point_count > 10000;";

    $sql .= "INSERT INTO `point_log` (`user_id`, `type`, `data`, `point`) SELECT id, 'daily', '', 100 - point_count FROM `users` WHERE point_count < 100;";
    $sql .= "UPDATE `users` SET `point_count` = 100 WHERE point_count < 100;";
    $sql .= "commit;";

    $mysqli = db_start();
    $mysqli->multi_query($sql);
    $err = $mysqli->error;
    $mysqli->close();
    if ($err) {
        disp_log($name, 2);
    } else {
        disp_log($name, 1);
    }
}

function rebuild_stat() {
    disp_log("management:rebuild_stat", 0);

    $mysqli = db_start();
    $stmt = $mysqli->prepare("SELECT * FROM `live` WHERE is_live = 0 AND is_started = 1;");
    $stmt->execute();
    $row = db_fetch_all($stmt);
    $stmt->close();
    $mysqli->close();

    $data = [];
    $all_length = count($row);

    foreach ($row as $index => $item) {
        echo "[Analyzing] " . ($index + 1) . " / " . $all_length . "...\n";

        if (!isset($data[$item["user_id"]])) {
            $data[$item["user_id"]] = [
                "viewers_max_concurrent" => 0,
                "viewers_count_max" => 0,
                "viewers_max" => 0,
                "comment_count_max" => 0,
                "comment_count_all" => 0,
                "point_count_max" => 0,
                "point_count_all" => 0,
                "time_max" => 0,
                "time_all" => 0
            ];
        }

        $time = strtotime($item["ended_at"]) - strtotime($item["created_at"]);
        if ($time < 0) {
            $time = 0;
        }

        $data[$item["user_id"]]["viewers_max"] += $item["viewers_max"];
        $data[$item["user_id"]]["comment_count_all"] += $item["comment_count"];
        $data[$item["user_id"]]["point_count_all"] += $item["point_count"];
        $data[$item["user_id"]]["time_all"] += $time;

        if ($item["viewers_max_concurrent"] > $data[$item["user_id"]]["viewers_max_concurrent"]) {
            $data[$item["user_id"]]["viewers_max_concurrent"] = $item["viewers_max_concurrent"];
        } //同時
        if ($item["viewers_max"] > $data[$item["user_id"]]["viewers_count_max"]) {
            $data[$item["user_id"]]["viewers_count_max"] = $item["viewers_max"];
        } //来場
        if ($item["comment_count"] > $data[$item["user_id"]]["comment_count_max"]) {
            $data[$item["user_id"]]["comment_count_max"] = $item["comment_count"];
        }
        if ($item["point_count"] > $data[$item["user_id"]]["point_count_max"]) {
            $data[$item["user_id"]]["point_count_max"] = $item["point_count"];
        }
        if ($time > $data[$item["user_id"]]["time_max"]) {
            $data[$item["user_id"]]["time_max"] = $time;
        }
    }

    $mysqli = db_start();
    $stmt = $mysqli->prepare("SELECT * FROM `users` WHERE `broadcaster_id` IS NOT NULL;");
    $stmt->execute();
    $row = db_fetch_all($stmt);
    $stmt->close();
    $mysqli->close();
    $all_length = count($row);

    foreach ($row as $index => $value) {
        echo "[Saving] " . ($index + 1) . " / " . $all_length . "...\n";
        if (!isset($data[$value["id"]])) {
            echo "[Skipping]\n";
            continue;
        }
        $value["misc"] = json_decode($value["misc"], true);
        $value["misc"] = $data[$value["id"]] + $value["misc"];
        setConfig($value["id"], $value["misc"]);
    }

    disp_log("management:rebuild_stat", 1);
}
