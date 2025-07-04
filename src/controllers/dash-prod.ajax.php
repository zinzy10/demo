<?php
session_start();
include '../models/dash-prod.class.php';
$res = new dashData();

switch ($_GET['opc']) {
    case 1:
        echo $res->Charts();
    break;
    case 2:
        echo $res->table();
    break;
}
?>