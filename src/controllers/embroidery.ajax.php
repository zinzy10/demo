<?php
session_start();
include '../models/embroidery.class.php';
$res = new dashData();

switch ($_GET['opc']) {
    case 1:
        echo $res->embF3();
        break;
}
?>