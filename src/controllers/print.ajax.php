<?php
session_start();
include '../models/print.class.php';
$res = new dashData();
 
switch ($_GET['opc']) {
    case 1:
        echo $res->allPrintF1();
        break;
    case 2:
        echo $res->allPrintF7();
        break;
    case 3:
        echo $res->allTotals();
        break;
    case 4:
        echo $res->printF1();
        break;
    case 5:
        echo $res->printF7();
        break;
    case 6:
        echo $res->totalPrint();
        break;
}
?>