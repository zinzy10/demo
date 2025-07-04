<?php
session_start();
include '../models/sales.class.php';
$res = new dashData();

switch ($_GET['opc']) {
    case 1:
        echo $res->salesCharts();
        break;
    case 2:
        echo $res->chartsByYear();
        break;
    case 3:
        echo $res->ClientChart();
        break;
    case 4:
        echo $res->listYear();
        break;
    // case 5:
    //     echo $res->update();
    //     break;       
    case 6:
        echo $res->actualizarGoals();
        break;
    case 7:
        echo $res->updateSales();
        break;
    case 8:
        echo $res->tableClient();
        break;
    case 9:
        echo $res->multiUpdate();
        break;
}

?>