<?php
session_start();
include '../src/models/sales.class.php';

$obj = new controlDB("prod","");
$res = new dashData();
$resClient = $res->selectClient();
$resultado = $res->selectYears();
$data = parse_ini_file("../../env/.env");
$BD = $data['prefixC'];

$clients = $resClient['clients'];
$selectY = $resultado['selectYears'];
$user = $_SESSION['username'];
$sql = "SELECT us_admin, production FROM {$BD}usuarios.usuarios INNER JOIN {$BD}usuarios.permission ON usuarios.id_usuario = permission.id_user WHERE username = :user";
$permiso = $obj->consultar($sql, [':user'=>$user]);
$production = $permiso[0]['production'];
$admin = $permiso[0]['us_admin'];

if (!isset($_SESSION['username'])) {
    header("location: ../index.php");
} else if ($production == '1') {
    $array = ['per'=>$production];
    echo json_encode($array);
    exit;
} else {

$html = '<div class="row mt-4">
            <div class="col-md-6 my-4">
                <div class="card">
                    <div class="card-header p-0 position-relative mt-n4 mx-3 bg-transparent">
                        <div class="dark-mode shadow-dark radius-lg py-3 pe-1">
                            <div class="chart">
                                <canvas id="chart-sales" class="chart-canvas" height="130"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <h5 class="fw-bold">Total Sales per month</h5>
                        <hr class="dark horizontal">
                        <p class="fst-italic">Choose the years to compare</p>
                        <div class="d-flex" id="selects">
                            <select id="year1" class="form-select form-select-sm">
                                <option selected disabled value="0">Select a Year</option>
                                '.$selectY.'
                            </select>
                            <select id="year2" class="form-select form-select-sm ms-3">
                                <option selected disabled value="0">Select a Year</option>
                                '.$selectY.'
                            </select>
                            <div class="d-flex" id="buttons">
                                <button type="button" class="btn btn-success btn-sm ms-3" title="Search" onclick="chartMonth()">
                                    <i class="fa-regular fa-magnifying-glass fa-md"></i>
                                </button>
                                <button type="button" class="btn btn-primary btn-sm ms-2" title="Clean" onclick="cleanSales()">
                                    <i class="fa-regular fa-rotate-left fa-md"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 my-4">
                <div class="card">
                    <div class="card-header p-0 position-relative mt-n4 mx-3 bg-transparent">
                        <div class="dark-mode shadow-dark radius-lg py-3 pe-1">
                            <div class="chart">
                                <canvas id="salesClients" class="chart-canvas" height="130"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <h5 class="fw-bold">Sales per Clients</h5>
                        <hr class="dark horizontal">
                        <p class="fst-italic">Choose the Client</p>
                        <div class="d-flex" id="selects2">
                            <select id="clients" class="form-select form-select-sm" onclick="clientList()">
                                <option selected disabled value="0">Select a Client</option>
                                '.$clients.'
                            </select>
                            <select id="year3" class="form-select form-select-sm ms-3">
                                <option selected disabled value="0">Select a Year</option>
                            </select>
                            <select id="year4" class="form-select form-select-sm ms-3">
                                <option selected disabled value="0">Select a Year</option>
                            </select>
                            <div class="d-flex" id="buttons2">
                                <button type="button" class="btn btn-success btn-sm ms-3" title="Search" onclick="chartsClients()">
                                    <i class="fa-regular fa-magnifying-glass fa-md"></i>
                                </button>
                                <button type="button" class="btn btn-primary btn-sm ms-2" title="Clean" onclick="cleanClients()">
                                    <i class="fa-regular fa-rotate-left fa-md"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row me-1">
            <div class="mb-md-0 mb-4">
                <div class="card mt-4 mb-5">
                    <div class="card-header p-0 position-relative mt-n4 mx-4">
                        <div class="bg-orange shadow-orange radius-lg pt-3 pb-2 row">
                            <h4 class="text-white fw-bold ps-3 col-10">Clients</h4>
                        </div>
                    </div>
                    <div class="card-body px-2 pb-2">
                        <div class="table-responsive">
                            <table id="tableClient" class="table compact hover">
                                <thead>
                                    <tr>
                                        <th class="text-secondary">Client</th>
                                        <th class="text-center text-secondary">'.(date('Y')-3).'</th>
                                        <th class="text-center text-secondary">'.(date('Y')-2).'</th>
                                        <th class="text-center text-secondary">'.(date('Y')-1).'</th>
                                        <th class="text-center text-secondary">'.date('Y').'</th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <th>TOTAL</th>
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
        <script src="../assets/js/clients.js"></script>';
    
    $code = ['html'=>$html];

    echo json_encode($code);
}
?>