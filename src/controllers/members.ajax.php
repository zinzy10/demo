<?php
session_start();
include '../models/members.class.php';
$res = new user();

switch ($_GET['opc']) {
    case 1:
        echo $res->users();
        break;
    case 2:
        echo $res->update_permission();
        break;
    case 3:
        echo $res->add_user();
        break;
    case 4:
        echo $res->registers();
        break;
    case 5:
        echo $res->check_user();
        break;
}
?>