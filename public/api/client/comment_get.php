<?php
require_once("../../../lib/bootloader.php");
require_once("../../../lib/apiloader.php");

$post = comment_get(s($_GET["id"]));

$i = 0;
while (isset($post[$i])) {
  $acct[$i] = getUser($post[$i]["user_id"]);
  $post[$i] = [
    "id" => "knzklive_".$post[$i]["id"],
    "live_id" => $post[$i]["live_id"],
    "is_knzklive" => true,
    "account" => [
      "display_name" => $acct[$i]["name"],
      "acct" => $acct[$i]["acct"]." (local)",
      "username" => $acct[$i]["acct"]." (local)",
      "avatar" => $acct[$i]["misc"]["avatar"],
      "url" => $acct[$i]["misc"]["user_url"]
    ],
    "content" => $post[$i]["content"],
    "created_at" => dateHelper($post[$i]["created_at"])
  ];
  $i++;
}

api_json($post);
