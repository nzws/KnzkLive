<?php
$env["Title"] = "KnzkLive : 広告なし, 配信時間無制限の生配信コミュニティ！";

$env["RootUrl"] = "/";
$env["domain"] = "live.knzk.me.example";

$env["notification_token"] = "xxxx"; //@KnzkLiveNotificationのトークン

$env["masto_login"]["domain"] = "knzk.me"; //本拠地にするインスタンス
$env["masto_login"]["redirect_uri"] = "https://live.knzk.me/login";

$env["tw_login"]["key"] = "";
$env["tw_login"]["secret"] = "";
$env["tw_login"]["redirect_uri"] = "https://live.knzk.me/auth/twitter";

// config.js と同じものを使用してください。
$env["database"]["host"] = "localhost";
$env["database"]["port"] = 3306;
$env["database"]["db"] = "dbname";
$env["database"]["user"] = "Username";
$env["database"]["pass"] = "Password";

$env["publish_auth"] = "xxxxx";

$env["is_testing"] = false;

// メンテナンスモード: 全てのAPIとWeb UIをロックし503にします(キャッシュ分は表示されるかも)
$env["is_maintenance"] = false;

$env["websocket_url"] = "http://localhost:3000";

// 管理者のID
$env["admin_id"] = 1;
