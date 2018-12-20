<?php
session_start();

if (!defined('DIR_SYSTEM')) {
    define("DIR_SYSTEM", $_SESSION["DIR_SYSTEM"]);
}

if (!defined('DIR_IMAGE')) {
    define("DIR_IMAGE", $_SESSION["DIR_IMAGE"]);
}

require_once('ReportPdfLog.php');
require_once('HealthCheck.php');

$config = $_SESSION["config"];

$healthcheck = new HealthCheck($config);
$resp = $healthcheck->setInitTransaction();
echo json_encode($resp);
