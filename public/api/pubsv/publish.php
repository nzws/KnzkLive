<?php
require_once "../../../lib/bootloader.php";
require_once "../../../lib/apiloader.php";

$id = strstr(s($_POST["stream"]), 'stream', true);
$live = getLive($id);
if (!$live || $live["is_live"] === 0) {
    showError('Authentication failed.', 403);
}

$publishToken = str_replace('?token=', '', $_POST["param"]);

if (strpos($_POST["stream"], 'collabo') !== false) { // collabo
    $collabo_id = strstr(str_replace($id . 'stream', '', s($_POST["stream"])), 'collabo', true);
    if ($publishToken !== $live["misc"]["collabo"][$collabo_id]["token"]) {
        showError('Authentication failed.', 403);
    }

    if ($_GET["mode"] === "on_publish") { //配信開始
        setCollaboLiveStatus($collabo_id, $live["id"], 2);
        api_json(["is_record" => false]);
    } elseif ($_GET["mode"] === "on_unpublish") { //配信終了
        setCollaboLiveStatus($collabo_id, $live["id"], 1);
    }
} else { // main
    if ($publishToken !== $live["token"]) {
        showError('Authentication failed.', 403);
    }

    if ($_GET["mode"] === "on_publish") { //配信開始
        setLiveStatus($live["id"], 2);
    } elseif ($_GET["mode"] === "on_unpublish") { //配信終了
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
