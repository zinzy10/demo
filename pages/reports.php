<?php
session_start();
include '../src/models/received.class.php';

$obj = new controlDB("prod","");
$data = parse_ini_file("../../env/.env");
$BD = $data['prefixC'];

$user = $_SESSION['username'];
$sql = "SELECT us_admin, us_view FROM {$BD}usuarios.usuarios INNER JOIN {$BD}usuarios.permission ON usuarios.id_usuario = permission.id_user WHERE username = :user";
$permiso = $obj->consultar($sql, [':user'=>$user]);
$reportAdmin = $permiso[0]['us_admin'];
$report = $permiso[0]['us_view'];

if (!isset($_SESSION['username'])) {
    header("location: ../index.php");
}

    $html = '<div class="col-md-10 col-lg-7 col-xl-6 mt-3">
                <div class="card">
                    <div class="card-header text-center fw-bold fs-5">
                        Orders Received
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-10 col-sm-6">
                                <select id="select_option" class="form-select form-select-sm" aria-label="select a date option" onchange="selectOption()">
                                    <option selected disabled value="0">Select a date option</option>
                                    <option value="1">Today</option>
                                    <option value="2">Yesterday</option>
                                    <option value="3">This Week</option>
                                    <option value="4">Last Week</option>
                                    <option value="5">This Month</option>
                                    <option value="6">Last Month</option>
                                    <option value="7">Date Range</option>
                                </select>
                                <div id="resultado_fechas" class="my-3">
                                    <div id="range" style="display: none;">
                                        <div class="input-group input-group-sm my-3">
                                            <span class="input-group-text">
                                                <i class="fa-regular fa-calendar fa-lg me-1"></i>Initial Date
                                            </span>
                                            <input type="date" id="initial_date" class="form-control form-control-sm" max="2025-06-23">
                                        </div>
                                        <div class="input-group input-group-sm mt-3">
                                            <span class="input-group-text">
                                                <i class="fa-regular fa-calendar fa-lg me-1"></i>Final Date
                                            </span>
                                            <input type="date" id="final_date" class="form-control form-control-sm" max="2025-06-23">
                                        </div>
                                        <div id="validationDate" class="invalid-feedback text-start">Select a date please</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-5 col-sm-3">
                                <div class="form-check">
                                    <input class="form-check-input checkBoxes" type="checkbox" name="checkbox" id="pending" value="check_pending">
                                    <label class="form-check-label" for="pending">Pending</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input checkBoxes" type="checkbox" name="checkbox" id="schedule" value="check_schedule">
                                    <label class="form-check-label" for="schedule">Schedule</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input checkBoxes" type="checkbox" name="checkbox" id="hold" value="check_hold">
                                    <label class="form-check-label" for="hold">Hold</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input checkBoxes" type="checkbox" name="checkbox" id="packed" value="check_packed">
                                    <label class="form-check-label" for="packed">Packed</label>
                                </div>
                            </div>
                            <div class="col-5 col-sm-3">
                                <div class="form-check">
                                    <input class="form-check-input checkBoxes" type="checkbox" name="checkbox" id="no ct" value="check_no_ct">
                                    <label class="form-check-label" for="no ct">No CT</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input checkBoxes" type="checkbox" name="checkbox" id="shipped" value="check_shipped">
                                    <label class="form-check-label" for="shipped">Shipped</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input checkBoxes" type="checkbox" name="checkbox" id="canceled" value="check_canceled">
                                    <label class="form-check-label" for="canceled">Canceled</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input checkBoxes" type="checkbox" name="checkbox" id="select_all" value="all" onclick="checkBox()">
                                    <label class="form-check-label" for="select_all">Select All</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-center">
                        <button type="button" class="btn btn-success btn-sm" title="Search" onclick="sendData()">
                            <i class="fa-regular fa-magnifying-glass fa-md"></i>
                        </button>
                        <button type="button" class="btn btn-primary btn-sm" title="Clean" onclick="clean()">
                            <i class="fa-regular fa-rotate-left fa-md"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-4 me-1">
            <div>
                <div class="card my-4">
                    <div class="card-header p-0 position-relative mt-n4 mx-4">
                        <div class="bg-orange shadow-orange radius-lg pt-3 pb-2 row">
                            <h4 class="col-10 text-white ps-3 fw-bold">Order Received</h4>
                            <div class="col-2 dropdown text-end mt-1">
                                <a id="menu_send" class="text-body p-0" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fa-regular fa-ellipsis fa-2xl text-white me-2 cursor-pointer"></i>
                                </a>
                                <ul class="modal-hov dropdown-menu dropdown-menu-end" aria-labelledby="menu_send">
                                    <li class="mb-2 mt-2">
                                        <a href="#" class="dropdown-item radius-lg" data-bs-toggle="modal" data-bs-target="#reports">
                                            <i class="fa-regular fa-file-pdf fa-xl ms-2"></i>
                                            <span class="fw-bold text-lg ms-2">Reports</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div id="reports" class="modal fade" tabindex="-1" aria-hidden="false" data-bs-backdrop="static">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 id="uploadModalLabel" class="modal-title fw-bold">Choose a Report</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="close"></button>
                                </div>
                                <form>
                                    <div class="modal-body">
                                        <div class="form-group">
                                            <select id="reportes" name="reportes" class="form-select form-select-sm" onchange="alert()">
                                                <option selected disabled value="0">Select a Report</option>
                                                <option value="2">Summary</option>
                                                <option disabled value="3">Upload Orders (coming soon)</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                    <button type="button" class="btn btn-sm btn-danger fw-bold" data-bs-dismiss="modal">Cancel</button>
                                        <button type="button" class="btn btn-sm btn-primary fw-bold" onclick="reports()">Next</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div id="pdf" class="modal fade" tabindex="-1" aria-hidden="false" data-bs-focus="false" aria-labelledby="uploadModalLabel"  data-bs-backdrop="static">
                        <div class="modal-dialog">
                            <div class="modal-content" id="changes-report">
                            </div>
                        </div>
                    </div>
                    <div id="dataTable" class="modal fade" tabindex="-1" aria-hidden="false" data-bs-backdrop="static">
                        <div class="modal-dialog modal-sm">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 id="uploadModalLabel" class="modal-title fw-bold">Select data to generate</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="close"></button>
                                </div>
                                <form>
                                    <div class="modal-body">
                                        <div class="form-group">
                                            <div>
                                                <fieldset id="listEdit">
                                                    <legend id="legend">Client / Store</legend>
                                                </fieldset>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                    <button type="button" id="previous" class="btn btn-sm btn-secondary fw-bold" data-bs-target="#pdf" data-bs-toggle="modal">Previous</button>
                                        <button type="button" id="generatePDF" class="btn btn-sm btn-primary fw-bold" onclick="editPDF()">Generate PDF</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="card-body px-2 pb-2">
                        <div class="table-responsive">
                            <table id="myTable1" class="table compact hover">
                                <thead>
                                    <tr>
                                        <th class="text-secondary">Client</th>
                                        <th class="text-center text-secondary">Total Amount</th>
                                        <th class="text-center text-secondary">Amount %</th>
                                        <th class="text-center text-secondary">Units</th>
                                        <th class="text-center text-secondary">Units %</th>
                                        <th class="text-center text-secondary">Hits</th>
                                        <th class="text-center text-secondary">Hits %</th>
                                        <th class="text-center text-secondary">Items</th>
                                        <th class="text-center text-secondary">Items %</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <script src="../assets/js/received.js"></script>';
            
    $code = ['html'=>$html];

    echo json_encode($code);
?>    