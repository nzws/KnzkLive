<?php
require_once("../../../lib/bootloader.php");
require_once("../../../lib/pubsvapiloader.php");

if ($_GET["mode"] === "post_play") { //視聴開始
  setViewersCount($live["id"], true);
  if ($live["viewers_max_concurrent"] < getLive($live["id"])["viewers_count"]) {
    $mysqli = db_start();
    $stmt = $mysqli->prepare("UPDATE `live` SET viewers_max_concurrent = viewers_count WHERE id = ?;");
    $stmt->bind_param("s", $live["id"]);
    $stmt->execute();
    $err = $stmt->error;
    $stmt->close();
    $mysqli->close();
  }
} elseif ($_GET["mode"] === "done_play") { //視聴終了
  setViewersCount($live["id"]);
}