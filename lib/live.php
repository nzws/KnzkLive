<?php
function getAbleSlot() {
    global $env;
    $mysqli = db_start();
    $stmts = $mysqli->prepare("SELECT * FROM `live_slot` WHERE is_testing = ? AND used < max;");
    $stmts->bind_param("s", $env["is_testing"]);
    $stmts->execute();
    $row = db_fetch_all($stmts);
    $stmts->close();
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
        $stmts = $mysqli->prepare("UPDATE `live_slot` SET used = used + 1 WHERE id = ?;");
    } else {
        $stmts = $mysqli->prepare("UPDATE `live_slot` SET used = used - 1 WHERE id = ?;");
    }
    $stmts->bind_param("s", $id);
    $stmts->execute();
    $stmts->close();
    $mysqli->close();
}

function getSlot($id) {
    global $env;
    $mysqli = db_start();
    $stmts = $mysqli->prepare("SELECT * FROM `live_slot` WHERE id = ?;");
    $stmts->bind_param("s", $id);
    $stmts->execute();
    $row = db_fetch_all($stmts);
    $stmts->close();
    $mysqli->close();
    return $row[0]["id"] ? $row[0] : false;
}

function getLive($id) {
    $mysqli = db_start();
    $stmts = $mysqli->prepare("SELECT * FROM `live` WHERE id = ?;");
    $stmts->bind_param("s", $id);
    $stmts->execute();
    $row = db_fetch_all($stmts);
    $stmts->close();
    $mysqli->close();
    return $row[0]["id"] ? $row[0] : false;
}