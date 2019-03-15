<?php
function load($argv) {
  if ($argv[1] === "daily") {
    merge_toot_point();
  } elseif ($argv[1] === "tipknzk") {
    $my = getUser($argv[3], "acct");
    if (empty($my)) exit("[Error] あなたのアカウントは存在しません。 https://live.knzk.me/ にログインしてください。");
    if (intval($argv[2]) > $my["point_count"] || intval($argv[2]) <= 0 || !$argv[2] || !is_numeric($argv[2])) exit("[Error] 残高が足りないかデータが不正です！ you don't have enough point!");
    $u = getUser($argv[4], "acct");
    if ($u["id"] === $my["id"]) exit("[Error] 自分自身には送信できません");
    if ($u) {
      $n = add_point($my["id"], $argv[2] * -1, "user", $u["acct"] . "にプレゼント");
      if ($n) {
        $o = add_point($u["id"], $argv[2], "user", $my["acct"] . "からのプレゼント (TIPKnzk)");
        if ($o) echo "@{$u["acct"]} に {$argv[2]}KP 送りました！ you sent {$argv[2]}KP to @{$u["acct"]}!";
        else exit("[Error] 例外エラー");
      }
    } else {
      exit("[Error] 相手のKnzkPointアカウントが存在しません！　https://live.knzk.me/ にログインする必要があります！");
    }
  } elseif ($argv[1] === "donate") {
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

  $sql .= "INSERT INTO `point_log` (`user_id`, `type`, `data`, `point`) SELECT id, 'daily', '', 100 - point_count FROM `users` WHERE point_count < 100;";
  $sql .= "UPDATE `users` SET `point_count` = 100 WHERE point_count < 100;";
  $sql .= "commit;";

  $mysqli = db_start();
  $mysqli->multi_query($sql);
  $err = $mysqli->error;
  $mysqli->close();
  if ($err) disp_log($name, 2);
  else disp_log($name, 1);
}
