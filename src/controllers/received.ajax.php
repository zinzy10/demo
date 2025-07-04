<?php
session_start();
include '../models/received.class.php';
$res = new reportes();

switch ($_GET['opc']) {
    case 1:
        echo $res->allOrders();
    break;
    case 2:
        echo $res->received();
    break;
    case 3:
        echo $res->clients();
    break;
    case 4:
        echo $res->options();
    break;
    case 5:
        echo $res->tableEdit();
    break;
}
?>