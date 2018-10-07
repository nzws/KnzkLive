<?php
require_once("../lib/bootloader.php");
$code = s($_GET["code"]);
if (!$code) {
    header("Location: https://".$env["masto_login"]["domain"]."/oauth/authorize?response_type=code&redirect_uri=".$env["masto_login"]["redirect_uri"]."&scope=read+write&client_id=".$env["masto_login"]["key"]);
    exit();
} else {
    $data = [
        "client_id" => $env["masto_login"]["key"],
        "client_secret" => $env["masto_login"]["secret"],
        "grant_type" => "authorization_code",
        "redirect_uri" => $env["masto_login"]["redirect_uri"],
        "code" => $code
    ];

    $header = [
        'Content-Type: application/json'
    ];

    $options = array('http' => array(
    'method' => 'POST',
    'content' => json_encode($data),
    'header' => implode(PHP_EOL,$header)
    ));
    $options = stream_context_create($options);
    $contents = file_get_contents("https://".$env["masto_login"]["domain"]."/oauth/token", false, $options);
    $json = json_decode($contents,true);
    if ($json["access_token"]) {
        $header = [
            'Authorization: Bearer '.$json["access_token"],
            'Content-Type: application/json'
        ];
        $options = array('http' => array(
        'method' => 'GET',
        'header' => implode(PHP_EOL,$header)
        ));
        $options = stream_context_create($options);
        $contents = file_get_contents("https://".$env["masto_login"]["domain"]."/api/v1/accounts/verify_credentials", false, $options);
        $json_acct = json_decode($contents,true);
        if ($json_acct["id"]) {
            $mysqli = db_start();
            $acct = s($json_acct["acct"]."@".$env["masto_login"]["domain"]);
            if ($user = getUser($acct, "acct")) {
              $misc = $user["misc"];
              $misc["avatar"] = $json_acct["avatar_static"];
              $misc["header"] = $json_acct["header_static"];
              $misc = json_encode($misc);

                $stmt = $mysqli->prepare("UPDATE `users` SET `name` = ?, `ip` = ?, `misc` = ?  WHERE `acct` = ?;");
                $stmt->bind_param('ssss', s($json_acct["display_name"]), $_SERVER["REMOTE_ADDR"], $misc, $acct);
            } else { //新規
              $misc["avatar"] = $json_acct["avatar_static"];
              $misc["header"] = $json_acct["header_static"];
              $misc = json_encode($misc);
                $stmt = $mysqli->prepare("INSERT INTO `users` (`id`, `name`, `acct`, `created_at`, `ip`, `isLive`, `liveNow`, `misc`) VALUES (NULL, ?, ?, CURRENT_TIMESTAMP, ?, '0', '0', ?);");
                $stmt->bind_param('ssss', s($json_acct["display_name"]), $acct, $_SERVER["REMOTE_ADDR"], $misc);
            }
            $stmt->execute();
            $stmt->close();
            $mysqli->close();
            $_SESSION["token"] = $json["access_token"];
            $_SESSION["acct"] = $acct;
            header("Location: ".$env["RootUrl"]);
        } else {
            err(2, $contents);
        }
    } else {
        err(1, $contents);
    }
}

function err($type, $data) {
    http_response_code(500);
     var_dump($data);
    exit("ERR:Mastodonからデータが取得できませんでした:".$type);
}