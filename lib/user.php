<?php
function getUser($id, $mode = "") {
    if (!$id) return false;
    $mysqli = db_start();
    if ($mode === "acct") {
        $stmts = $mysqli->prepare("SELECT * FROM `users` WHERE acct = ?;");
    } else {
        $stmts = $mysqli->prepare("SELECT * FROM `users` WHERE id = ?;");
    }
    $stmts->bind_param("s", $id);
    $stmts->execute();
    $row = db_fetch_all($stmts);
    $stmts->close();
    $mysqli->close();

    return $row[0]["id"] ? $row[0] : false;
}

function getMe() {
    return $_SESSION["acct"] ? getUser($_SESSION["acct"], "acct") : false;
}

function setUserLive($id) {
    //if (!$id) return false;
    $mysqli = db_start();
    $stmts = $mysqli->prepare("UPDATE `users` SET liveNow = ? WHERE acct = ?;");
    $stmts->bind_param("ss", $id, $_SESSION["acct"]);
    $stmts->execute();
    $stmts->close();
    $mysqli->close();
}