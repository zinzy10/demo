<?php
session_start();
include '../models/validation.class.php';
$res = new profile();

switch ($_GET['opc']) {
    case 1:
        echo $res->changePass();
        break;
    case 2:
        echo $res->validation();
        break;
}

?>