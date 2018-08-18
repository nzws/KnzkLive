<?php
function getAbleSlot() {
    global $env;
    $mysqli = db_start();
    $stmt = $mysqli->prepare("SELECT * FROM `live_slot` WHERE is_testing = ? AND used < max;");
    $testing = $env["is_testing"] ? 1 : 0;
    $stmt->bind_param("s", $testing);
    $stmt->execute();
    $row = db_fetch_all($stmt);
    $stmt->close();
    $mysqli->close();
    if ($row[0]["id"]) {
        return $row[0]["id"];
    } else {
        return false;
    }
}

function setSlot($id, $mode) {
    global $env;
    $mysqli = db_start();
    if ($mode) {
        $stmt = $mysqli->prepare("UPDATE `live_slot` SET used = used + 1 WHERE id = ?;");
    } else {
        $stmt = $mysqli->prepare("UPDATE `live_slot` SET used = used - 1 WHERE id = ?;");
    }
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $stmt->close();
    $mysqli->close();
}

function getSlot($id) {
    global $env;
    $mysqli = db_start();
    $stmt = $mysqli->prepare("SELECT * FROM `live_slot` WHERE id = ?;");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $row = db_fetch_all($stmt);
    $stmt->close();
    $mysqli->close();
    return $row[0]["id"] ? $row[0] : false;
}

function getLive($id) {
    $mysqli = db_start();
    $stmt = $mysqli->prepare("SELECT * FROM `live` WHERE id = ?;");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $row = db_fetch_all($stmt);
    $stmt->close();
    $mysqli->close();
    return $row[0]["id"] ? $row[0] : false;
}