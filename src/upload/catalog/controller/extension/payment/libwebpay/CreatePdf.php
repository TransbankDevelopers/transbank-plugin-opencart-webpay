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

$document = $_GET["document"];
$healthcheck = new HealthCheck($config);

$json = $healthcheck->printFullResume();
$temp = json_decode($json);
if ($document == "report"){
    unset($temp->php_info);
} else {
    $temp = array('php_info' => $temp->php_info);
}
$rl = new ReportPdfLog($document);
$rl->getReport(json_encode($temp));

