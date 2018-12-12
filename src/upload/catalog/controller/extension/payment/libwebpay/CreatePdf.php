<?php
require_once('ReportPdfLog.php');
require_once('HealthCheck.php');

if (!isset($_POST['item']) or empty($_POST['item'])) {
    exit;
}
$document = $_POST["document"];
$objeto = $_POST['item'];

$temp = json_decode($objeto);
if ($document == "report") {
    unset($temp->php_info);
} else {
    $temp = array('php_info' => $temp->php_info);
}

$rl = new ReportPdfLog($document);
$rl->getReport(json_encode($temp));
