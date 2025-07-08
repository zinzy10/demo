<?php
session_start();
include '../models/tables.class.php';
$res = new reportes();

switch ($_GET['opc']) {
    case 1:
        echo $res->prints();
        break;
    case 2:
        echo $res->labels();
        break;
    case 3:
        echo $res->treatment();
        break;
    case 4:
        echo $res->embroidery();
        break;
    case 5:
        echo $res->shipped();
        break;
    case 6:
        echo $res->received();
        break;
}

?>