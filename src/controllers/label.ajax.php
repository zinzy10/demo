<?php
session_start();
include '../models/label.class.php';
$res = new dashData();

switch ($_GET['opc']) {
    case 1:
        echo $res->padf1();
        break;
    case 2:
        echo $res->padf3();
        break;
    case 3:
        echo $res->padf7();
        break;
    case 4:
        echo $res->totalPad();
        break;
}
?>