<?php
function add_donate($live_id, $user_id, $amount, $currency) {
  $yen = ex_rate($amount, $currency);
  if (!$yen) return false;

  if ($yen < 500) {
    $color = "deepskyblue";
    $min = 3;
  } elseif ($yen < 1000) {
    $color = "lightseagreen";
    $min = 5;
  } else {
    $color = "red";
    $min = 10;
  }

  $end = date("Y-m-d H:i:s", strtotime("+{$min} minutes"));

  $mysqli = db_start();
  $stmt = $mysqli->prepare("INSERT INTO `donate` (live_id, user_id, amount, currency, ended_at, color) VALUES (?, ?, ?, ?, ?, ?);");
  $stmt->bind_param('ssssss', $live_id, $user_id, $amount, $currency, $end, $color);
  $stmt->execute();
  $err = $stmt->error;
  $id = $mysqli->insert_id;
  $stmt->close();
  $mysqli->close();

  if ($err) return false;
  send_donate_ws($live_id, $user_id, $amount, $currency, $end, $color, $id);
  $value = s($amount . $currency);
  comment_post("<div class=\"alert alert-warning\">{$value} 支援しました！</div>", $user_id, $live_id, true);

  return true;
}

function get_donate($live_id) {
  $mysqli = db_start();
  $stmt = $mysqli->prepare("SELECT * FROM `donate` WHERE live_id = ? AND ended_at > NOW() ORDER BY id desc;");
  $stmt->bind_param("s", $live_id);
  $stmt->execute();
  $row = db_fetch_all($stmt);
  $stmt->close();
  $mysqli->close();
  return isset($row[0]["id"]) ? $row : false;
}

function send_donate_ws($live_id, $user_id, $amount, $currency, $end, $color, $id) {
  global $env;

  $data = [
    "id" => $id,
    "type" => "donate",
    "live_id" => $live_id,
    "account" => user4Pub(getUser($user_id)),
    "amount" => s($amount),
    "currency" => s($currency),
    "ended_at" => date("Y-m-d H:i:s", strtotime($end)),
    "color" => $color
  ];

  $header = [
    'Content-Type: application/json'
  ];

  $options = array('http' => array(
    'method' => 'POST',
    'content' => json_encode($data),
    'header' => implode(PHP_EOL,$header)
  ));
  $options = stream_context_create($options);
  $contents = file_get_contents($env["websocket_url"]."/send_prop", false, $options);
  return !!$contents;
}

function ex_rate($amount, $currency) {
  //todo: どっかのAPIでリアタイ為替レート
  $rate = [
    "USD" => 110,
    "RUB" => 1.5,
    "EUR" => 120,
    "JPY" => 1
  ];
  return isset($rate[$currency]) ? intval($amount * $rate[$currency]) : false;
}
