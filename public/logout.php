<?php
require_once "../lib/bootloader.php";

// http://php.net/manual/ja/function.session-destroy.php
$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}
session_destroy();
header("Location: " . $env["RootUrl"]);
