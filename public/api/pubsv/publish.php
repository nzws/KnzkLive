<?php
require_once "../../../lib/bootloader.php";
require_once "../../../lib/apiloader.php";

$post = json_decode(file_get_contents("php://input"), true);
$id = strstr(s($post["stream"]), 'stream', true);
$live = getLive($id);
if (!$live || $live["is_live"] === 0) {
    showError('Authentication failed.', 403);
}

$publishToken = str_replace('?token=', '', $post["param"]);

if (strpos($post["stream"], 'collabo') !== false) { // collabo
    $collabo_id = strstr(str_replace($id . 'stream', '', s($post["stream"])), 'collabo', true);
    if ($publishToken !== $live["misc"]["collabo"][$collabo_id]["token"]) {
        showError('Authentication failed.', 403);
    }

    if ($post["action"] === "on_publish") { //配信開始
        setCollaboLiveStatus($collabo_id, $live["id"], 2);
        api_json(["is_record" => false]);
    } elseif ($post["action"] === "on_unpublish") { //配信終了
        setCollaboLiveStatus($collabo_id, $live["id"], 1);
    }
} else { // main
    if ($publishToken !== $live["token"]) {
        showError('Authentication failed.', 403);
    }

    if ($post["action"] === "on_publish") { //配信開始
        setLiveStatus($live["id"], 2);
    } elseif ($post["action"] === "on_unpublish") { //配信終了
        $liveUser = getUser($live["user_id"]);
        if (isset($liveUser["misc"]["auto_close"]) && $liveUser["misc"]["auto_close"] && $live["is_started"] == 1) {
            end_live($live["id"]);
        } else {
            setLiveStatus($live["id"], 1);
        }
    }
}

http_response_code(200);
echo 0;
