<?php
session_start();
include '../models/capacity.class.php';
$res = new dashData();

switch ($_GET['opc']) {
    case 1:
        echo $res->capacity();
        break;
    case 2:
        echo $res->components();
        break;
    case 3:
        echo $res->tableComp();
        break;
    case 4:
        echo $res->tableIncomp();
        break;
}
?>