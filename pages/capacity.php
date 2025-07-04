<?php
session_start();
include '../config/controlDB_PDO.php';

$obj = new controlDB("prod","");
$data = parse_ini_file("../../env/.env");
$BD = $data['prefixC'];

$user = $_SESSION['username'];
$sql = "SELECT production FROM {$BD}usuarios.usuarios INNER JOIN {$BD}usuarios.permission ON usuarios.id_usuario = permission.id_user WHERE username = :user";
$permiso = $obj->consultar($sql, [':user'=>$user]);
$permit = $permiso[0]['production'];

if (!isset($_SESSION['username'])) {
    header("location: ../index.php");
} else if ($permit == '1') {
    $array = ['per'=>$permit];
    echo json_encode($array);
    exit;
} else {

$html = '<div class="row mt-5 me-1">
            <div id="calendar"></div>
        </div>
        <div class="modal fade" id="infoCapacity" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header" id="title"></div>
                    <div class="modal-body">
                        <h5>Orders Complete<button type="button" class="ms-2 btn button-small btn-primary" onclick="complete()">View Orders</button></h5>
                        <div class="ms-2" id="complete">
                            <p class="mb-0">Styles:</p>
                            <p>Hits:</p>
                        </div>
                        <hr class="horizontal dark">
                        <h5>Orders not Complete<button type="button" class="ms-2 btn button-small btn-primary" onclick="incomplete()">View Orders</button></h5>
                        <div class="ms-2" id="incomplete">
                            <p class="mb-0">Styles:</p>
                            <p>hits:</p>
                        </div>
                        <input type="hidden" id="modalDate">
                    </div>
                    <div class="modal-footer">
                        <h5 class="m-auto d-flex align-items-baseline col-9 col-sm-10">Total Hits: <input class="input col-5" id="total" disabled></h5>
                        <button type="button" class="btn btn-sm btn-danger" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <div id="Tcomplete" class="modal fade" tabindex="-1" aria-labelledby="registerLabel" aria-hidden="true">
            <div class="modal-dialog modal-fullscreen">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 id="registerLabel" class="modal-title fw-bold">Orders Complete <input class="dates fw-bold" id="modalDate1" disabled></h3>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table id="tcomplete" class="table compact hover">
                                <thead>
                                    <tr>
                                        <th class="text-center text-secondary">Status</th>
                                        <th class="text-center text-secondary">Ship Date</th>
                                        <th class="text-center text-secondary">Client</th>
                                        <th class="text-center text-secondary">Store</th>
                                        <th class="text-center text-secondary">PO/Lot</th>
                                        <th class="text-center text-secondary">Cxl Date</th>
                                        <th class="text-center text-secondary">Style</th>
                                        <th class="text-center text-secondary">Description</th>
                                        <th class="text-center text-secondary">Color</th>
                                        <th class="text-center text-secondary">Gender</th>
                                        <th class="text-center text-secondary">Hits</th>
                                        <th class="text-center text-secondary">Treatment</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-primary fw-bold" data-bs-dismiss="modal">Ok</button>
                    </div>
                </div>
            </div>
        </div>
        <div id="Tincomplete" class="modal fade" tabindex="-1" aria-labelledby="registerLabel" aria-hidden="true">
            <div class="modal-dialog modal-fullscreen">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 id="registerLabel" class="modal-title fw-bold">Orders not Complete <input class="dates fw-bold" id="modalDate2" disabled></h3>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table id="tincomplete" class="table compact hover">
                                <thead>
                                    <tr>
                                        <th class="text-center text-secondary">Approve</th>
                                        <th class="text-center text-secondary">Status</th>
                                        <th class="text-center text-secondary">Ship Date</th>
                                        <th class="text-center text-secondary">Client</th>
                                        <th class="text-center text-secondary">Store</th>
                                        <th class="text-center text-secondary">PO/Lot</th>
                                        <th class="text-center text-secondary">Cxl Date</th>
                                        <th class="text-center text-secondary">Style</th>
                                        <th class="text-center text-secondary">Description</th>
                                        <th class="text-center text-secondary">Color</th>
                                        <th class="text-center text-secondary">Gender</th>
                                        <th class="text-center text-secondary">Hits</th>
                                        <th class="text-center text-secondary">Treatment</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-primary fw-bold" data-bs-dismiss="modal">Ok</button>
                    </div>
                </div>
            </div>
        </div>
        <script src="../assets/js/capacity.js"></script>';

        $code = ['html'=>$html];
        echo json_encode($code);
}
?>