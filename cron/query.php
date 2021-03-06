<?php

require_once "../classes/MinecraftPing.class.php";
require_once "../classes/Database.class.php";
require_once "../classes/StatsCollector.class.php";
$dbData = require_once "../config/database.config.php";

$db = new Database($dbData["user"], $dbData["pass"], $dbData["dbname"]);

$collector = new StatsCollector($db);
$stats = $collector->saveAllHourlyStats();

var_dump($stats);