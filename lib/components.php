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