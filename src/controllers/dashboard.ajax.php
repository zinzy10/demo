<?php
session_start();
include '../models/dashboard.class.php';
$res = new dashData();

switch ($_GET['opc']) {
    case 1:
        echo $res->updateData();
        break;
    case 2:
        echo $res->received();
        break;
}
?>