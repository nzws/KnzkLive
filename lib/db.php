<?php
function db_start() {
    global $env;
    $mysqli = new mysqli($env["database"]["host"], $env["database"]["user"], $env["database"]["pass"], $env["database"]["db"], $env["database"]["port"]);
    if ($mysqli->connect_errno) {
        http_response_code(500);
        echo('[DBERR]');
        exit();
    }
    $mysqli->set_charset("utf8mb4");
    return $mysqli;
}

function db_fetch_all(& $stmt) {
    $hits = array();
    $params = array();
    $meta = $stmt->result_metadata();
    while ($field = $meta->fetch_field()) {
        $params[] = &$row[$field->name];
    }
    call_user_func_array(array($stmt, 'bind_result'), $params);
    while ($stmt->fetch()) {
        $c = array();
        foreach($row as $key => $val) {
            $c[$key] = $val;
        }
        $hits[] = $c;
    }
    return $hits;
}