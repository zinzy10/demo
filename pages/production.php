<?php
session_start();
include '../src/models/dash-prod.class.php';

$obj = new controlDB("prod","");
$data = parse_ini_file("../../env/.env");
$BD = $data['prefixC'];

$user = $_SESSION['username'];
$sql = "SELECT name, production FROM {$BD}usuarios.usuarios INNER JOIN {$BD}usuarios.permission ON usuarios.id_usuario = permission.id_user WHERE username = :user";
$permiso = $obj->consultar($sql, [':user'=>$user]);
$name = ucwords($permiso[0]['name']);

if (!isset($_SESSION['username'])) {
    header("location: ../index.php");
}

    $html = '<div class="d-flex flex-column h-100">
                <h1 class="col-sm-6 col-md-5 col-xl-12 fw-semibold">Hello, '.$name.'</h1>
                <p class="text-dark fs-6">Here\'s a summary of your activity for this week.</p>
            </div>
            <div class="row mt-2">
                <div class="col-md-6 col-xl-4 my-4">
                    <div class="card">
                        <div class="card-header p-0 position-relative mt-n4 mx-3 bg-transparent">
                            <div class="loader radius-lg py-3 pe-1">
                                <div class="chart">
                                    <canvas id="printing" class="chart-canvas"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <h6 class="mb-0 fw-bold">Total Printing</h6>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-4 my-4">
                    <div class="card">
                        <div class="card-header p-0 position-relative mt-n4 mx-3 bg-transparent">
                            <div class="loader radius-lg py-3 pe-1">
                                <div class="chart">
                                    <canvas id="treatment" class="chart-canvas"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <h6 class="mb-0 fw-bold">Total Treatment</h6>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 mt-4 mb-3">
                    <div class="card">
                        <div class="card-header p-0 position-relative mt-n4 mx-3 bg-transparent">
                            <div class="loader radius-lg py-3 pe-1">
                                <div class="chart">
                                    <canvas id="label" class="chart-canvas"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <h6 class="mb-0 fw-bold">Total Pad Print</h6>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="mb-md-0">
                    <div  class="card mt-4">
                        <div class="card-header p-0 position-relative mt-n4 mx-3">
                            <div class="bg-orange shadow-orange radius-lg pt-3 pb-2">
                                <h4 class="text-white fw-bold ps-3">Production</h4>
                            </div>
                        </div>
                        <div class="card-body px-2 pb-2">
                            <div class="table-responsive">
                                <table id="production" class="table compact hover">
                                    <thead>
                                        <tr>
                                            <th class="text-secondary">Process</th>
                                            <th class="text-center text-secondary">1st Quality</th>
                                            <th class="text-center text-secondary">Mill Damage</th>
                                            <th class="text-center text-secondary">Prod. Damage</th>
                                            <th class="text-center text-secondary">Total</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <script src="../assets/js/production.js"></script>';

            // <div class="row mt-3">
            //     <div class="mb-md-0">
            //         <div  class="card mt-4">
            //             <div class="card-header p-0 position-relative mt-n4 mx-3">
            //                 <div class="bg-orange shadow-orange radius-lg pt-3 pb-2">
            //                     <h4 class="text-white fw-bold ps-3">Schedule Today</h4>
            //                 </div>
            //             </div>
            //             <div class="card-body px-2 pb-2">
            //                 <div class="table-responsive">
            //                     <table id="" class="table compact hover">
            //                         <thead>
            //                             <tr>
            //                                 <th class="text-secondary">Press</th>
            //                                 <th class="text-center text-secondary">Hit</th>
            //                                 <th class="text-center text-secondary">Style</th>
            //                                 <th class="text-center text-secondary">PO/LOT</th>
            //                                 <th class="text-center text-secondary">Hits Qty</th>
            //                             </tr>
            //                         </thead>
            //                     </table>
            //                 </div>
            //             </div>
            //         </div>
            //     </div>
            // </div>

    $code = ['html'=>$html];

    echo json_encode($code);
?>