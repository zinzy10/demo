<?php
session_start();
include '../config/controlDB_PDO.php';

$obj = new controlDB("prod","");
$data = parse_ini_file("../../env/.env");
$BD = $data['prefixC'];

$user = $_SESSION['username'];
$sql = "SELECT us_admin, us_view, production, edit FROM {$BD}usuarios.usuarios INNER JOIN {$BD}usuarios.permission ON usuarios.id_usuario = permission.id_user WHERE username = :user";
$permiso = $obj->consultar($sql, [':user'=> $user]);
$admin = $permiso[0]['us_admin'];
$view = $permiso[0]['us_view'];
$permit = $permiso[0]['production'];

if (!isset($_SESSION['username'])) {
    header("location: ../index.php");
} else if ($permit == '1') {
    $array = ['per'=>$permit];
    echo json_encode($array);
    exit;
// } else if ($view == '1') {
//     $array = ['per'=>$view];
//     echo json_encode($array);
//     exit;
} else {

    $disabled = "disabled";
    if ($admin == '1' || $edit = '1'){
        $disabled = '';
    }

$html ='<div class="row mt-5 me-1">
            <div class="d-flex justify-content-end">
                <input type="hidden" id="disabled" value="'.$disabled.'">
                <button type="button" class="btn btn-primary btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#addUser"'.$disabled.'>
                    <i class="fa-regular fa-user-plus fa-md me-1"></i>Add User</button>
            </div>
            <div id="addUser" class="modal fade" tabindex="-1" aria-labelledby="addUserModal" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold">Members Information</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label">Name</label>
                                    <input type="text" class="form-control" id="name">
                                    <span id="name-msg"></span>
                                </div>
                                <div class="col-md-6">
                                    <label for="lastName" class="form-label">Laste Name</label>
                                    <input type="text" class="form-control" id="lastName">
                                    <span id="lastName-msg"></span>
                                </div>
                                <div class="col-md-6">
                                    <label for="username" class="form-label">Username</label>
                                    <input type="text" class="form-control" id="username">
                                    <span id="username-msg"></span>
                                </div>
                                <div class="col-md-6">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="text" class="form-control" id="password">
                                </div>
                                <div class="input-group">
                                    <input type="email" class="form-control" placeholder="Email" id="email">
                                    <span class="input-group-text">@email.com</span>
                                </div>
                                <div class="col-md-6">
                                    <label for="puesto" class="form-label">Puesto</label>
                                    <input type="text" class="form-control" id="puesto">
                                    <span id="puesto-msg"></span>
                                </div>
                            </div>
                        </div>  
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-sm btn-primary fw-bold" id="sendUser" onclick="add_user()">Add</button>
                        </div>
                    </div>
                </div>
            </div>
            <div>
                <div class="card my-3">
                    <div class="card-body px-2 pb-2">
                        <div class="table-responsive">
                            <table id="users" class="table compact hover">
                                <thead>
                                    <tr>
                                        <th class="text-center text-secondary">#</th>
                                        <th class="text-secondary">Username</th>
                                        <th class="text-secondary">Name</th>
                                        <th class="text-center text-secondary">Email</th>
                                        <th class="text-center text-secondary">Position</th>
                                        <th class="text-center text-secondary">Acces</th>
                                        <th class="text-center text-secondary">Admin</th>
                                        <th class="text-center text-secondary">Edit</th>
                                        <th class="text-center text-secondary">View</th>
                                        <th class="text-center text-secondary">Production</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>   
        <script src="../assets/js/members.js"></script>';

    $code = ['html'=>$html];

    echo json_encode($code); 
} ?>
