<?php
require_once("../../../../lib/bootloader.php");
require_once("../../../../lib/apiloader.php");

resetVoteHist(s($_GET["id"]));
