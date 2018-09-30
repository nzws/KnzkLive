<?php
function u($p = "") {
  global $env;
  if (!$p && $env["is_testing"]) $p = "index";
  return $env["RootUrl"].$p.($env["is_testing"] ? ".php" : "");
}

function s($p) {
  return htmlspecialchars($p, ENT_QUOTES|ENT_HTML5);
}