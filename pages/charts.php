<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("location: ../index.php");
}

    $html = '<div class="row mt-4">
                <div class="col-md-6 my-4">
                    <div class="card">
                        <div class="card-header p-0 position-relative mt-n4 mx-3 bg-transparent">
                            <div class="loader shadow-dark radius-lg py-3 pe-1">
                                <div class="chart">
                                    <canvas id="chartPrints" class="chart-canvas" height="130"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <h5 class="fw-bold">Total Prints</h5>
                            <p class="fw-light fst-italic">Quantities of impressions made based on the finish print</p>
                            <hr class="dark horizontal">
                            <div class="d-flex col-8 col-sm-4 col-md-6 col-lg-4">
                                <select id="factory" class="form-select form-select-sm" aria-label="select a facility" onchange="printChart()">
                                    <option selected disabled value="">Select a Facility</option>
                                    <option value="1">All</option>
                                    <option value="2">Press 1</option>
                                    <option value="3">Press 2</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 my-4">
                    <div class="card">
                        <div class="card-header p-0 position-relative mt-n4 mx-3 bg-transparent">
                            <div class="loader shadow-dark radius-lg py-3 pe-1">
                                <div class="chart">
                                    <canvas id="chartTreatment" class="chart-canvas" height="130"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <h5 class="fw-bold">Total Treatment</h5>
                            <p class="fw-light fst-italic">Total quantities treated</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 my-4">
                    <div class="card">
                        <div class="card-header p-0 position-relative mt-n4 mx-3 bg-transparent">
                            <div class="loader shadow-dark radius-lg py-3 pe-1">
                                <div class="chart">
                                    <canvas id="chartEmb" class="chart-canvas" height="130"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <h5 class="fw-bold">Total Embroidery</h5>
                            <p class="fw-light fst-italic">Total quantities embroidered</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 my-4">
                    <div class="card">
                        <div class="card-header p-0 position-relative mt-n4 mx-3 bg-transparent">
                            <div class="loader shadow-dark radius-lg py-3 pe-1">
                                <div class="chart">
                                    <canvas id="chartLabel" class="chart-canvas" height="130"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <h5 class="fw-bold">Total labels</h5>
                            <p class="fw-light fst-italic">Total quantities of label printed</p>
                            <hr class="dark horizontal">
                            <div class="d-flex col-8 col-sm-4 col-md-6 col-lg-4">
                                <select id="factory2" class="form-select form-select-sm" aria-label="select a facility" onchange="labelChart()">
                                    <option selected disabled value="">Select a Facility</option>
                                    <option value="ALL">All</option>
                                    <option value="MP1">Press 1</option>
                                    <option value="MP8">Press 2</option>
                                    <option value="MP7">Press 3</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 my-4">
                    <div class="card">
                        <div class="card-header p-0 position-relative mt-n4 mx-3 bg-transparent">
                            <div class="loader shadow-dark radius-lg py-3 pe-1">
                                <div class="chart">
                                    <canvas id="chartHits" class="chart-canvas" height="130"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <h5 class="fw-bold">Total Hits</h5>
                            <p class="fw-light fst-italic">Quantities of hits received based on the date received</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 my-4">
                    <div class="card">
                        <div class="card-header p-0 position-relative mt-n4 mx-3 bg-transparent">
                            <div class="loader shadow-dark radius-lg py-3 pe-1">
                                <div class="chart">
                                    <canvas id="chartBlanks" class="chart-canvas" height="130"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <h5 class="fw-bold">Total Blanks FP</h5>
                            <p class="fw-light fst-italic">Quantities of blanks received</p>
                        </div>
                    </div>
                </div>
            </div>
            <script src="../assets/js/charts.js"></script>';
            
    $code = ['html'=>$html];

    echo json_encode($code);
?>