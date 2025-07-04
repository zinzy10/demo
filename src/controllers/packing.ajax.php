<?php
session_start();
include '../models/packing.class.php';
$res = new dashData();

switch ($_GET['opc']) {
    case 1:
        echo $res->packingF1();
        break;
    case 2:
        echo $res->packingF7();
        break;
    case 3:
        echo $res->totalPack();
        break;
}
?>