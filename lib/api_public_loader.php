<?php
require_once __DIR__ . "bootloader.php";
require_once __DIR__ . "apiloader.php";

function return_api_error($text, $http_status = 200) {
    if ($http_status !== 200) {
        http_response_code($http_status);
    }
    api_json(["error" => $text]);
}

function array_acct_public(array $accts) {
    foreach ($accts as $key => $acct) {
        $accts[$key] = user4Pub($acct);
    }
    return $accts;
}

$headers = getallheaders();
if (!empty($allow_nologin) || isset($headers['token'])) {
    $my = getUser($headers['token'], "token");
    if (!$my) {
        return_api_error("認証に失敗しました。", 403);
    }
}
