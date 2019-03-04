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

function userUrl($id) {
  global $env;
  return (empty($_SERVER["HTTPS"]) ? "http://" : "https://") . $_SERVER["HTTP_HOST"] .($env["is_testing"] ?  u("user") . "?id=" : $env["RootUrl"] . "user/") . $id;
}

function assetsUrl() {
  global $env;
  return (empty($env["assets_url"]) ? $env["RootUrl"] : $env["assets_url"]);
}

function liveTag($live) {
  return $live["custom_hashtag"] ? $live["custom_hashtag"] : "knzklive_".$live["id"];
}

function HTMLHelper($text, $options = []) {
  $text = s($text);
  $text = !empty($options["ignore_nl"]) ? $text : nl2br($text);
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

function showError($text, $http_status = null) {
  global $errortext;

  if (!empty($http_status)) http_response_code($http_status);
  $errortext = $text;
  include __DIR__ . "/../include/errorpage.php";
}
