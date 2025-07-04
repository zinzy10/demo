<?php
session_start();
include '../src/models/dashboard.class.php';

$obj = new controlDB("prod","");
$res = new dashData();
$res->cardsData();
$data = parse_ini_file("../../env/.env");
$BD = $data['prefixC'];

$user = $_SESSION['username'];
$sql = "SELECT name, production FROM {$BD}usuarios.usuarios INNER JOIN {$BD}usuarios.permission ON usuarios.id_usuario = permission.id_user WHERE username = :user";
$permiso = $obj->consultar($sql, [':user'=>$user]);
$name = ucwords($permiso[0]['name']);
$production = $permiso[0]['production'];

if (!isset($_SESSION['username'])) {
    header("location: ../index.php");
} else if ($production == '1') {
    $array = ['per'=>$production];
    echo json_encode($array);
    exit;
} else {

    $html = '<div class="d-flex flex-column h-100">
                <h1 class="col-sm-6 col-md-5 col-xl-4 fw-semibold">Hello, '.$name.'</h1>
                <p class="text-dark fs-6">Here\'s a summary of your activity.</p>
            </div>
            <div class="row mt-3">
                <div class="col-sm-6 col-lg-3 mb-xl-0 mb-4">
                    <div class="card">
                        <div class="card p-3 pt-2">
                            <div class="icon-lg bg-orange shadow-orange text-center radius-xl mt-n4 position-absolute">
                                <i class="fa-solid fa-dollar-sign"></i>
                            </div>
                            <div class="text-end pt-1">
                                <p class="text-sm fst-italic mb-0">Today\'s Money</p>
                                <h4 id="tamount" class="mb-0 fw-bold">'.$res->Tamount.'</h4>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3 mb-xl-0 mb-4">
                    <div class="card">
                        <div class="card p-3 pt-2">
                            <div class="icon-lg bg-orange shadow-orange text-center radius-xl mt-n4 position-absolute">
                                <i class="fa-solid fa-file-invoice"></i>
                            </div>
                            <div class="text-end pt-1">
                                <p class="text-sm fst-italic mb-0">Orders Received</p>
                                <h4 id="orders" class="mb-0 fw-bold">'.$res->orders.'</h4>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3 mb-xl-0 mb-4">
                    <div class="card">
                        <div class="card p-3 pt-2">
                            <div class="icon-lg bg-orange shadow-orange text-center radius-xl mt-n4 position-absolute">
                                <i class="fa-solid fa-shirt"></i>
                            </div>
                            <div class="text-end pt-1">
                                <p class="text-sm fst-italic mb-0">Blanks Received</p>
                                <h4 id="blanks" class="mb-0 fw-bold">'.$res->blanks.'</h4>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3 mb-xl-0 mb-4">
                    <div class="card">
                        <div class="card p-3 pt-2">
                            <div class="icon-lg bg-orange shadow-orange text-center radius-xl mt-n4 position-absolute">
                                <i class="fa-solid fa-palette"></i>
                            </div>
                            <div class="text-end pt-1">
                                <p class="text-sm fst-italic mb-0">Hits Received</p>
                                <h4 id="hits" class="mb-0 fw-bold">'.$res->hits.'</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-md-6 col-xl-4 my-4">
                    <div class="card">
                        <div class="card-header p-0 position-relative mt-n4 mx-3 bg-transparent">
                            <div class="loader radius-lg py-3 pe-1">
                                <div class="chart">
                                    <canvas id="chart-line3" class="chart-canvas"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <h6 class="mb-0 fw-bold">Total amount per Quarter</h6>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-4 my-4">
                    <div class="card">
                        <div class="card-header p-0 position-relative mt-n4 mx-3 bg-transparent">
                            <div class="loader radius-lg py-3 pe-1">
                                <div class="chart">
                                    <canvas id="chart-line1" class="chart-canvas"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <h6 class="mb-0 fw-bold">Total amount per Months</h6>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 mt-4 mb-3">
                    <div class="card">
                        <div class="card-header p-0 position-relative mt-n4 mx-3 bg-transparent">
                            <div class="loader radius-lg py-3 pe-1">
                                <div class="chart">
                                    <canvas id="chart-line2" class="chart-canvas"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <h6 class="mb-0 fw-bold">Total amount per Week</h6>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="mb-md-0">
                    <div  class="card mt-4">
                        <div class="card-header p-0 position-relative mt-n4 mx-3">
                            <div class="bg-orange shadow-orange radius-lg pt-3 pb-2">
                                <h4 class="text-white fw-bold ps-3">Orders Received Today</h4>
                            </div>
                        </div>
                        <div class="card-body px-2 pb-2">
                            <div class="table-responsive">
                                <table id="received" class="table compact hover">
                                    <thead>
                                        <tr>
                                            <th class="text-secondary">Client</th>
                                            <th class="text-center text-secondary">Total Amount</th>
                                            <th class="text-center text-secondary">POs</th>
                                            <th class="text-center text-secondary">Items</th>
                                            <th class="text-center text-secondary">Blanks</th>
                                            <th class="text-center text-secondary">Hits</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <script src="../assets/js/statistics.js"></script>';

    $code = ['html'=>$html];

    echo json_encode($code); 
} ?>