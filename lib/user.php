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
    
    if (isset($row[0]["id"])) {
        $row[0]["misc"] = json_decode($row[0]["misc"], true);
    }

    return isset($row[0]["id"]) ? $row[0] : false;
}

function getMe() {
    return isset($_SESSION["acct"]) ? getUser($_SESSION["acct"], "acct") : false;
}

function setUserLive($id) {
    $mysqli = db_start();
    $stmt = $mysqli->prepare("UPDATE `users` SET liveNow = ? WHERE acct = ?;");
    $stmt->bind_param("ss", $id, $_SESSION["acct"]);
    $stmt->execute();
    $stmt->close();
    $mysqli->close();
}