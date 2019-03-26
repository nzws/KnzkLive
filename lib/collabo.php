<?php
function is_collabo($user_id, $live_id)
{
    $live = getLive($live_id);
    if (!$live) {
        return false;
    }

    return isset($live["misc"]["collabo"][$user_id]);
}

function setCollaboLiveStatus($user_id, $live_id, $status = 0)
{
    $live = getLive($live_id);
    if (!$live) {
        return false;
    }

    if (!isset($live["misc"]["collabo"][$user_id])) {
        return false;
    }

    update_realtime_config("update_collabo_status", [
        "user_id" => $user_id,
        "status" => $status
    ], $live["id"]);

    $live["misc"]["collabo"][$user_id]["status"] = $status;
    setLiveConfig($live["id"], $live["misc"]);
}
