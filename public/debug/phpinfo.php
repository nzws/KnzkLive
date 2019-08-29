<?php
require_once "../../lib/bootloader.php";

if (empty($env['is_testing'])) {
    exit();
}

phpinfo();
