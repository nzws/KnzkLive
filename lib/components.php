<?php
function u($p = "") {
  global $env;
  if (!$p && $env["is_testing"]) $p = "index";
  return $env["RootUrl"].$p.($env["is_testing"] ? ".php" : "");
}

function s($p) {
  return htmlspecialchars($p, ENT_QUOTES|ENT_HTML5);
}

function liveUrl($id) {
  global $env;
  return (empty($_SERVER["HTTPS"]) ? "http://" : "https://") . $_SERVER["HTTP_HOST"] .($env["is_testing"] ?  u("live") . "?id=" : u("watch")) . $id;
}

function liveTag($live) {
  return $live["custom_hashtag"] ? $live["custom_hashtag"] : "knzklive_".$live["id"];
}

function HTMLHelper($text) {
  $text = s($text);
  $text = nl2br($text);
  $text = preg_replace("/(https?:\/\/[-_.!~*\'()a-zA-Z0-9;\/?:@&=+$,%#]+)/", "<a href='\\1' rel='nofollow' target='_blank'>\\1</a>", $text);
  return $text;
}

function dateHelper($date) {
  return str_replace("-", "/", $date);
}

function dispSecDate($sec) {
  $text = "";
  $h = intval($sec / 3600);
  $m = intval(($sec / 60) % 60);
  $s = intval($sec % 60);

  if ($h > 0) $text .= ($h < 10 ? "0" : "") . $h . "時間";
  if ($m > 0) $text .= ($m < 10 ? "0" : "") . $m . "分";
  $text .= ($s < 10 ? "0" : "") . $s . "秒";

  return $text;
}
