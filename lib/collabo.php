<?php
function is_collabo($user_id, $live_id) {
  $live = getLive($live_id);
  if (!$live) return false;

  $user = getUser($user_id);
  if (!$user) return false;

  return array_search($user["id"], $live["misc"]["collabo"]) !== false;
}
