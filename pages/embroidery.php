<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("location: ../index.php");
}

    $html = '<div class="col-sm-10 col-md-8 col-lg-6 col-xxl-4 mt-3">
                <div class="card">
                    <div class="row p-2">
                        <div class="col-6 mt-4">
                            <select id="select_option" onchange="selectOption()" class="form-select form-select-sm" aria-label="select a date option">
                                <option selected disabled value="0">Select a date option</option>
                                <option value="1">Today</option>
                                <option value="2">Yesterday</option>
                                <option value="3">This Week</option>
                                <option value="4">Last Week</option>
                                <option value="5">This Month</option>
                                <option value="6">Last Month</option>
                                <option value="7">Date Range</option>
                            </select>
                            <div id="range" style="display: none;">
                                <div class="input-group input-group-sm mt-3">
                                    <span class="input-group-text">
                                        <i class="fa-regular fa-calendar fa-lg me-1"></i>Initial Date
                                    </span>
                                    <input id="initial_date" class="form-control form-control-sm" type="date" max="'.date('Y-m-d').'">
                                </div>
                                <div class="input-group input-group-sm mt-3">
                                    <span class="input-group-text">
                                        <i class="fa-regular fa-calendar fa-lg me-1"></i>Final Date
                                    </span>
                                    <input id="final_date" class="form-control form-control-sm" type="date" max="'.date('Y-m-d').'">
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mt-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="shift" id="day" value="day">
                                <label class="form-check-label" for="day">Shift Day</label>
                            </div>
                            <div class="form-chek">
                                <input class="form-check-input" type="checkbox" name="shift" id="night" value="night">
                                <label class="form-check-label" for="night">Shift Night</label>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-center">
                        <button type="button" class="btn btn-success btn-sm" onclick="sendData()" title="Search">
                            <i class="fa-regular fa-magnifying-glass fa-md"></i>
                        </button>
                        <button type="button" class="btn btn-primary btn-sm" onclick="clean()" title="Clean">
                            <i class="fa-regular fa-eraser fa-md"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="row mt-4">
                <div>
                    <div class="card mt-4">
                        <div class="card-header p-0 position-relative mt-n4 mx-3">
                            <div class="bg-orange shadow-orange radius-lg pt-3 pb-2">
                                <h4 class="text-white fw-bold ps-3">Press</h4>
                            </div>
                        </div>
                        <div class="card-body px-2 pb-2">
                            <div class="table-responsive">
                                <table id="BF3" class="table compact hover">
                                    <thead>
                                        <tr>
                                            <th class="text-secondary fw-bold">Press</th>
                                            <th class="text-secondary fw-bold">1st Quality</th>
                                            <th class="text-secondary fw-bold">Mill Damage</th>
                                            <th class="text-secondary fw-bold">Prod. Damage</th>
                                            <th class="text-secondary fw-bold">Total</th>
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
            <script src="../assets/js/embroidery.js"></script>';

    $code = ['html'=>$html];

    echo json_encode($code);
?>