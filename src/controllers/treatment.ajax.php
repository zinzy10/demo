<?php
session_start();
include '../models/treatment.class.php';
$res = new dashData();

switch ($_GET['opc']) {
    case 1:
        echo $res->treatment();
        break;
}
?>