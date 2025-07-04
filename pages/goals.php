<?php
session_start();
include '../config/controlDB_PDO.php';

$obj = new controlDB("prod","");
$data = parse_ini_file("../../env/.env");
$BD = $data['prefixC'];

$user = $_SESSION['username'];
$sql = "SELECT us_admin, production, edit FROM {$BD}usuarios.usuarios INNER JOIN {$BD}usuarios.permission ON usuarios.id_usuario = permission.id_user WHERE username = :user";
$permiso = $obj->consultar($sql, [':user'=>$user]);
$permit = $permiso[0]['production'];
$file = $permiso[0]['us_admin'];
$edit = $permiso[0]['edit'];

if (!isset($_SESSION['username'])) {
    header("location: ../index.php");
} else if ($permit == '1') {
    $array = ['per'=>$permit];
    echo json_encode($array);
    exit;
} else {

    $disabled = "disabled";
    if ($file == '1' || $edit == '1') {
        $disabled = "";
    }

$html ='<div class="row mt-4 me-1">
            <div>
                <div class="card mt-3">
                    <div class="card-header p-0 position-relative mt-n4 mx-4">
                        <div class="bg-orange shadow-orange radius-lg pt-3 pb-2 row">
                            <h4 class="text-white fw-bold ps-3 col-10">Goals</h4>';
                            if ($file == '1') {
                        $html .='<div class="col-2 dropdown text-end mt-1">
                                <a id="menu_update" class="text-body p-0" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fa-regular fa-ellipsis fa-2xl text-white cursor-pointer me-2"></i>
                                </a>
                                <ul class="modal-hov dropdown-menu dropdown-menu-end" aria-labelledby="menu_update">
                                    <li>
                                        <a href="#" class="dropdown-item radius-lg" data-bs-toggle="modal" data-bs-target="#uploadSales">
                                            <i class="fa-regular fa-file-arrow-up fa-xl ms-2"></i>
                                            <span class="fw-bold ms-2">Update File</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>';
                            } 
                $html .='</div>
                    </div>';
                    if ($file == '1') {
                $html .= '<div id="uploadSales" class="modal fade" tabindex="-1" aria-labelledby="uploadSalesLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 id="uploadSalesLabel" class="modal-title fs-5 fw-bold">Sale File</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label for="file" class="form-label">Select CSV</label>
                                        <input type="file" name="file" id="file" class="form-control form-control-sm mt-2 radius-lg" accept=".csv" onchange="removeError()">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" id="send" class="btn btn-primary btn-sm fw-bold" onclick="uploadFile()">Upload</button>
                                </div>
                            </div>
                        </div>
                    </div>';
                    }
                $html .= '<div id="editSales" class="modal fade" tabindex="-1" aria-labelledby="editSalesLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 id="editSalesLabel" class="modal-title fs-5 fw-bold">Edit Goals</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label  class="form-label">New Goal</label>
                                        <input type="number" id="goals" class="form-control form-control-sm radius-lg">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-primary btn-sm fw-bold" id="updateGoals" onclick="updateGoals()">OK</button>
                                    <button type="button" class="btn btn-secondary btn-sm fw-bold" data-bs-dismiss="modal">Cancel</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body px-2 pb-2">
                        <div class="table-responsive">
                            <table id="tableGoals" class="table compact hover">
                                <thead>
                                    <tr>
                                        <th class="button-center">
                                            <button class="btn btn-primary button-sm fw-bold" onclick="updateSelect()"'.$disabled.'>Edit</button>
                                        </th>
                                        <th class="text-secondary">Client</th>
                                        <th class="text-center text-secondary">Goals'.date('Y').'</th>
                                        <th class="text-center text-secondary">Current Sales</th>
                                        <th class="text-center text-secondary">Difference</th>
                                        <th class="text-center text-secondary">Monthly Goal</th>
                                        <th class="text-center text-secondary">Current Month Goal</th>
                                        <th class="text-center text-secondary">Current Month Difference</th>
                                        <th class="text-center text-secondary">Updated Monthly Goal</th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <th></th>
                                        <th>TOTAL</th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="../assets/js/goals.js"></script>';
    $code = ['html'=>$html];

    echo json_encode($code);
}
?>