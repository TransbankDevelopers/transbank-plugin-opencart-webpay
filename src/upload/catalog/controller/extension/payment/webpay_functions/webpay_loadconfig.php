<?php

require_once '../libwebpay/loghandler.php';

if (!isset($_POST['req']) or empty($_POST['req'])) {
  exit;
}



$objeto = $_POST['req'];
$obj = json_decode($objeto);
$comm =  $obj->ecommerce;
$lan = new LogHandler($comm);

//$lan->makeLogDir();
//echo $obj->status;
if (isset($_POST['update']) and $_POST['update'] == 'si') {
  echo "solo se actualiza";
  $lan->setnewconfig((integer)$obj->max_days, (integer)$obj->max_weight);
}else{
  if ($obj->status === true) {
    $lan->setLockStatus($obj->status);
    $lan->setnewconfig((integer)$obj->max_days, (integer)$obj->max_weight);
  }else{
    $lan->setLockStatus(false);
  }

}
