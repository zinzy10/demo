<?php
session_start();
include '../models/production.class.php';
$res = new production();

switch ($_GET['opc']) {
    case 1:
        echo $res->prints();
        break;
    case 2:
        echo $res->labels();
        break;
    case 3:
        echo $res->charts();
        break;
}

?>