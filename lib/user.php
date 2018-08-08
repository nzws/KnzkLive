<?php
function getUser($id, $mode = "") {
    if (!$id) return false;
    $mysqli = db_start();
    if ($mode === "acct") {
        $stmt = $mysqli->prepare("SELECT * FROM `users` WHERE acct = ?;");
    } else {
        $stmt = $mysqli->prepare("SELECT * FROM `users` WHERE id = ?;");
    }
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $row = db_fetch_all($stmt);
    $stmt->close();
    $mysqli->close();

    return $row[0]["id"] ? $row[0] : false;
}

function getMe() {
    return $_SESSION["acct"] ? getUser($_SESSION["acct"], "acct") : false;
}

function setUserLive($id) {
    //if (!$id) return false;
    $mysqli = db_start();
    $stmt = $mysqli->prepare("UPDATE `users` SET liveNow = ? WHERE acct = ?;");
    $stmt->bind_param("ss", $id, $_SESSION["acct"]);
    $stmt->execute();
    $stmt->close();
    $mysqli->close();
}