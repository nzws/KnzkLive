<?php
function u($p = "") {
    global $env;
    if (!$p && $env["is_testing"]) {
        $p = "index";
    }
    return $env["RootUrl"] . $p . ($env["is_testing"] ? ".php" : "");
}

function s($p) {
    return htmlspecialchars($p, ENT_QUOTES|ENT_HTML5);
}

function liveUrl($id) {
    global $env;
    return (empty($_SERVER["HTTPS"]) ? "http://" : "https://") . $_SERVER["HTTP_HOST"] . ($env["is_testing"] ?  u("live") . "?id=" : u("watch")) . $id;
}

function userUrl($id) {
    global $env;
    return (empty($_SERVER["HTTPS"]) ? "http://" : "https://") . $_SERVER["HTTP_HOST"] . ($env["is_testing"] ?  u("user") . "?id=" : $env["RootUrl"] . "user/") . $id;
}

function assetsUrl() {
    global $env;
    return (empty($env["assets_url"]) ? $env["RootUrl"] : $env["assets_url"]);
}

function liveTag($live) {
    return $live["custom_hashtag"] ? $live["custom_hashtag"] : "knzklive_" . $live["id"];
}

function HTMLHelper($text, $options = []) {
    $text = s($text);
    $text = !empty($options["ignore_nl"]) ? $text : nl2br($text);
    $text = preg_replace("/(https?:\/\/[-_.!~*\'()a-zA-Z0-9;\/?:@&=+$,%#]+)/", "<a href='\\1' rel='nofollow' target='_blank'>\\1</a>", $text);
    return $text;
}

function dateHelper($date) {
    return str_replace("-", "/", $date);
}

function dispSecDate($sec) {
    $text = "";
    $h = intval($sec / 3600);
    $m = intval(($sec / 60) % 60);
    $s = intval($sec % 60);

    if ($h > 0) {
        $text .= ($h < 10 ? "0" : "") . $h . "時間";
    }
    if ($m > 0) {
        $text .= ($m < 10 ? "0" : "") . $m . "分";
    }
    $text .= ($s < 10 ? "0" : "") . $s . "秒";

    return $text;
}

function showError($text, $http_status = null) {
    global $errortext;

    if (!empty($http_status)) {
        http_response_code($http_status);
    }
    $errortext = $text;
    include __DIR__ . "/../include/errorpage.php";
}

function is_admin($user_id) {
    global $env;
    return isset($env["admin_ids"]) && array_search($user_id, $env["admin_ids"]) !== false;
}

function sendToDiscord($data) {
    global $env;

    if (empty($env["report_discord_webhook_url"])) {
        return false;
    }

    $options = ['http' => [
        'method' => 'POST',
        'content' => json_encode($data),
        'header' => implode(PHP_EOL, ['Content-Type: application/json'])
    ]];

    $options = stream_context_create($options);
    return file_get_contents($env["report_discord_webhook_url"], false, $options) !== false;
}

function generateHash($retry = 0) {
    $hash = bin2hex(openssl_random_pseudo_bytes(32, $is_secure));
    return $is_secure || $retry > 5 ? $hash : generateHash($retry + 1);
}

function i($name, $type = "s") {
    return "<i class='fa{$type} fa-{$name} fa-fw'></i>";
}

function checkV($var, $min_length = 0, $max_length = 0) {
    $length = mb_strlen($var);
    return ($length >= $min_length && $length <= $max_length);
}

function UAParser() {
    if (empty($_SESSION["UA_CONF"])) {
        $_SESSION["UA_CONF"] = serialize(UAParser\Parser::create()->parse($_SERVER['HTTP_USER_AGENT']));
    }

    return unserialize($_SESSION["UA_CONF"]);
}
