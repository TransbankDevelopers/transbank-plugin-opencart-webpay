<?php

require_once('LogHandler.php');

if (!isset($_POST['req']) or empty($_POST['req'])) {
  exit;
}

$objeto = $_POST['req'];
$obj = json_decode($objeto);
$lan = new LogHandler();

if (isset($_POST['update']) and $_POST['update'] == 'si') {
  $lan->setnewconfig((integer)$obj->max_days, (integer)$obj->max_weight);
}else{
  if ($obj->status === true) {
    $lan->setLockStatus($obj->status);
    $lan->setnewconfig((integer)$obj->max_days, (integer)$obj->max_weight);
  }else{
    $lan->setLockStatus(false);
  }
}
