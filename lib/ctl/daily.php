<?php
function merge_toot_point() {
  $name = "merge-toot-point";
  disp_log($name, 0);

  $sql = "start transaction;";
  $sql .= "INSERT INTO `point_log` (`user_id`, `type`, `data`, `point`) SELECT id, 'toot', '', CASE WHEN point_count_today_toot > 500 THEN 500 ELSE point_count_today_toot END FROM `users` WHERE point_count_today_toot > 0;";
  $sql .= "UPDATE `users` SET `point_count` = `point_count` + CASE WHEN point_count_today_toot > 500 THEN 500 ELSE point_count_today_toot END, `point_count_today_toot` = 0 WHERE point_count_today_toot > 0;";
  $sql .= "commit;";

  $mysqli = db_start();
  $mysqli->multi_query($sql);
  $err = $mysqli->error;
  $mysqli->close();
  if ($err) disp_log($name, 2);
  else disp_log($name, 1);
}
