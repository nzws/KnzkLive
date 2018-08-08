<?php
function u($p) {
  global $env;
  return $env["RootUrl"].$p;
}

function s($p) {
  return htmlspecialchars($p, ENT_QUOTES|ENT_HTML5);
}