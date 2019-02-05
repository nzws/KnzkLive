<?php
function load($argv) {
  $dir = __DIR__ . "/";
  if (!isset($argv[1])) $argv[1] = null;

  if ($argv[1] === "daily") {
    require_once $dir . "daily.php";
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
        if ($o) exit("@{$u["acct"]} に {$argv[2]}KP 送りました！ you sent {$argv[2]}KP to @{$u["acct"]}!");
        else exit("[Error] 例外エラー");
      }
    } else {
      exit("[Error] 相手のKnzkPointアカウントが存在しません！　https://live.knzk.me/ にログインする必要があります！");
    }
  } elseif ($argv[1] === "setup") {
    require_once $dir . "setup.php";
    run_setup();
  } else {
    disp_log("command '{$argv[1]}' is not found", 2);
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
