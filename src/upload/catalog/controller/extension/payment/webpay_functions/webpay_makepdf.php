<?php
$fileh =  '../libwebpay/tcpdf/reportPDFlog.php';
include_once($fileh);

if (!isset($_POST['item']) or empty($_POST['item'])) {
    exit;
}
$document = $_POST["document"];
$objeto = $_POST['item'];
$obj = json_decode($objeto);
$getpdf = new reportPDFlog($obj->server_resume->plugin_info->ecommerce, $document);
$temp = json_decode($objeto);
if ($document == "report") {
    unset($temp->php_info);
} else {
    $temp = array('php_info' => $temp->php_info);
}
$objeto = json_encode($temp);
$getpdf->getReport($objeto);
