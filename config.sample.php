<?php
$env["Title"] = "KnzkLive : 広告なし, 配信時間無制限の生配信コミュニティ！";

$env["RootUrl"] = "/";
$env["domain"] = "live.knzk.me.example";
$env["assets_url"] = ""; // https://assets.knzklive.example/
$env["is_testing"] = false;

$env["notification_token"] = "xxxx"; //@KnzkLiveNotificationのトークン

$env["masto_login"]["domain"] = "knzk.me"; //本拠地にするインスタンス
// $env["masto_login"]["redirect_uri"] = "https://live.knzk.me/login";
$env["masto_login"]["redirect_uri"] = "http" . (empty($env["is_testing"]) ? "s" : "") . "://" . $env["domain"] . $env["RootUrl"] . "login" . (empty($env["is_testing"]) ? "" : ".php");

$env["tw_login"]["key"] = "";
$env["tw_login"]["secret"] = "";
// $env["tw_login"]["redirect_uri"] = "https://live.knzk.me/auth/twitter";
$env["tw_login"]["redirect_uri"] = "http" . (empty($env["is_testing"]) ? "s" : "") . "://" . $env["domain"] . $env["RootUrl"] . "auth/twitter" . (empty($env["is_testing"]) ? "" : ".php");

$env["streamlabs"]["id"] = "";
$env["streamlabs"]["secret"] = "";
$env["streamlabs"]["redirect_uri"] = "http" . (empty($env["is_testing"]) ? "s" : "") . "://" . $env["domain"] . $env["RootUrl"] . "auth/streamlabs" . (empty($env["is_testing"]) ? "" : ".php");

// config.js と同じものを使用してください。
$env["database"]["host"] = "localhost";
$env["database"]["port"] = 3306;
$env["database"]["db"] = "dbname";
$env["database"]["user"] = "Username";
$env["database"]["pass"] = "Password";

$env["publish_auth"] = "xxxxx";

// メンテナンスモード: 全てのAPIとWeb UIをロックし503にします(キャッシュ分は表示されるかも)
$env["is_maintenance"] = false;

$env["websocket_url"] = "http://localhost:3000";

$env["report_discord_webhook_url"] = "";

// 管理者のID
$env["admin_id"] = 1;
