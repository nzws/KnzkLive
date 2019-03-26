<?php
function getItems($user_id, $type)
{
    global $cacheItems;
    if (isset($cacheItems[$user_id][$type])) {
        return $cacheItems[$user_id][$type];
    }
    $mysqli = db_start();
    $stmt = $mysqli->prepare("SELECT * FROM `items` WHERE user_id = ? AND type = ?;");
    $stmt->bind_param("ss", $user_id, $type);
    $stmt->execute();
    $row = db_fetch_all($stmt);
    $stmt->close();
    $mysqli->close();
    $cacheItems[$user_id][$type] = $row;

    return isset($row[0]["id"]) ? $row : [];
}

function getItem($id)
{
    $mysqli = db_start();
    $stmt = $mysqli->prepare("SELECT * FROM `items` WHERE id = ?;");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $row = db_fetch_all($stmt);
    $stmt->close();
    $mysqli->close();
    return isset($row[0]["id"]) ? $row[0] : false;
}

function getEmojis($user_id, $type)
{
    global $env;

    $mysqli = db_start();
    if ($type === "comment") {
        $stmt = $mysqli->prepare("SELECT * FROM `items` WHERE user_id = ? AND type = 'emoji' AND able_comment = 1;");
    } else {
        $stmt = $mysqli->prepare("SELECT * FROM `items` WHERE user_id = ? AND type = 'emoji' AND able_item = 1;");
    }
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $row = db_fetch_all($stmt);
    $stmt->close();
    $mysqli->close();
    $d = [];

    if ($type === "item") {
        $liveUser = getUser($user_id);
        $d = [
            [
                "url" => "https://twemoji.maxcdn.com/2/svg/1f44d.svg",
                "code" => "default_good"
            ],
            [
                "url" => "https://twemoji.maxcdn.com/2/svg/2764.svg",
                "code" => "default_heart"
            ],
            [
                "url" => "https://twemoji.maxcdn.com/2/svg/1f44f.svg",
                "code" => "default_clapping"
            ],
            [
                "url" => "https://twemoji.maxcdn.com/2/svg/1f389.svg",
                "code" => "default_popper"
            ],
            [
                "url" => "https://twemoji.maxcdn.com/2/svg/1f36e.svg",
                "code" => "default_pudding"
            ],
            [
                "url" => $liveUser["misc"]["avatar"],
                "code" => "default_avatar_liver"
            ]
        ];

        if ($my = getMe()) {
            $d[] = [
                "url" => $my["misc"]["avatar"],
                "code" => "default_avatar_me"
            ];
        }
    }

    if (isset($row[0]["id"])) {
        foreach ($row as $item) {
            $d[] = [
                "url" => $env["storage"]["root_url"] . "emoji/" . $item["file_name"],
                "code" => $item["name"]
            ];
        }
    }

    return $d;
}

function checkItemSlot($user_id, $type)
{
    $my = getUser($user_id);

    $limit = $my["misc"][$type . "_slot"] - count(getItems($my["id"], $type));
    if ($limit > 0) {
        return true;
    }

    if (!add_point($my["id"], $type === "emoji" ? -500 : -1500, "slot", "スロット追加: " . $type)) {
        return false;
    }

    $my["misc"][$type . "_slot"]++;
    setConfig($my["id"], $my["misc"]);
    return true;
}
