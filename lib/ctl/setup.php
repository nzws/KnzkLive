<?php
function run_setup() {
  echo "Welcome to the KnzkLive Setup!!!\n\n";

  $is_generate_configjson = confirm("config.json (Node用設定ファイル) も一緒に生成しますか？");
  $is_generate_phinxyml = confirm("phinx.yml (マイグレーション設定ファイル) も一緒に生成しますか？");

  $env["is_testing"] = confirm("本番環境として設定しますか？(yes: for production, no: for development)");

  $env["domain"] = prompt("ドメインを指定してください。(例えば、 https://example.com/KnzkLive/なら、 'example.com')", "live.knzk.me");
  $env["RootUrl"] = prompt("ルートURLを指定してください。(例えば、 https://example.com/KnzkLive/なら、 '/KnzkLive/')", "/");

  $env["masto_login"]["domain"] = prompt("メインで使用するMastodonサーバを指定してください。", "knzk.me");
  $env["masto_login"]["redirect_uri"] = "http" . ($env["is_testing"] ? "" : "s") . "://" . $env["domain"] . $env["RootUrl"] . "login" . ($env["is_testing"] ? ".php" : "");

  $env["notification_token"] = prompt("配信開始通知用アカウントのトークンを入力してください。");

  if ($is_generate_configjson) {
    $env["tipknzk"]["token"] = prompt("TIPKnzkアカウントのトークンを入力してください。");
    $env["tipknzk"]["id"] = prompt("TIPKnzkアカウントのユーザーIDを入力してください。(例えば、@tipknzkなら、'tipknzk')", "tipknzk");
  }

  if (confirm("Twitterログインの設定を行いますか？")) {
    $env["tw_login"]["key"] = prompt("API Keyを入力してください。");
    $env["tw_login"]["secret"] = prompt("API secret keyを入力してください。");
    $env["tw_login"]["redirect_uri"] = "http" . ($env["is_testing"] ? "" : "s") . "://" . $env["domain"] . $env["RootUrl"] . "auth/twitter" . ($env["is_testing"] ? ".php" : "");
  }

  $env["Title"] = "KnzkLive : 広告なし, 配信時間無制限の生配信コミュニティ！";
  $env["is_maintenance"] = false;
  $env["websocket_url"] = "http://localhost:3000";

  $env = db_setting($env);
}

function db_setting($env) {
  $env["database"]["host"] = prompt("DB: ホストを入力してください。", "localhost");
  $env["database"]["port"] = prompt("DB: ポート番号を入力してください。", 3306);
  $env["database"]["db"] = prompt("DB: データベース名を入力してください。", "knzklive");
  $env["database"]["user"] = prompt("DB: ユーザー名を入力してください。");
  $env["database"]["pass"] = prompt("DB: パスワードを入力してください。");

  if (confirm("データベースの接続テストを行いますか？")) {
    $mysqli = new mysqli($env["database"]["host"], $env["database"]["user"], $env["database"]["pass"], $env["database"]["db"], $env["database"]["port"]);
    $err = $mysqli->connect_errno;
    $mysqli->close();
    if ($err) {
      if (confirm("Error: データベースに接続できませんでした。もう一度設定しなおしますか？")) {
        return db_setting($env);
      } else {
        if (!confirm("そのまま続行してセットアップを行いますか？"))
          exit("exit: セットアップは完了しませんでした...");
      }
    } else {
      echo "データベース接続テストに成功しました！！！\n\n";

      if (confirm("データベースのセットアップを行いますか？")) {

      }
    }
  }

  return $env;
}

function prompt($text, $default = "") {
  echo $text . (!empty($default) ? " ({$default})" : "") . ": ";
  $handle = fopen("php://stdin","r");
  $line = fgets($handle);
  if (empty(trim($line)) && !empty($default)) $line = $default;
  elseif (empty(trim($line))) {
    echo "Error: 値が入力されていません。\n";
    return prompt($text, $default);
  }
  return trim($line);
}

function confirm($text) {
  return prompt($text, "yes") == "yes";
}
