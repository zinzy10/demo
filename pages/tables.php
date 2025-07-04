<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("location: ../index.php");
}

    $html = '<div class="row mt-3">
                <div class="col-sm-10 col-md-8 col-lg-6 col-xxl-4 mb-xl-0 mb-4">
                    <div class="card">
                        <div class="card-body ms-3">
                            <h4 class="fw-bold">Choose the Process</h4>
                            <div class="d-flex col-10 col-sm-6 col-xl-7">
                                <select id="reports" class="form-select form-select-sm" aria-label="choose a report" onchange="mostrarTabla()">
                                    <option selected value="1">Print</option>
                                    <option value="2">labels</option>
                                    <option value="3">Treatments</option>
                                    <option value="4">Embroidery</option>
                                    <option value="5">Shipped</option>
                                    <option value="6">Orders Received</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="mensaje" class="text-center" role="status"></div>
            <div id="tabla1" class="row mt-5" style="display: none;">
                <div class="mb-4">
                    <div class="card my-4">
                        <div class="card-header p-0 position-relative mt-n4 mx-3">
                            <div class="bg-orange shadow-orange radius-lg pt-3 pb-2">
                                <h4 class="text-white fw-bold ps-3">Prints</h4>
                            </div>
                        </div>
                        <div class="card-body px-2 pb-2">
                            <div class="table-responsive">
                                <table id="print" class="table table-sm">
                                    <thead>
                  	                    <tr>
                  	                        <th></th>
                  	                        <th class="text-center" style="background-color: #FAFAFA">'.(date('Y')-1).'</th>
                  	                        <th class="text-center">'.date('Y').'</th>
                  	                    </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="tabla2" class="row mt-5" style="display: none;">
                <div class="mb-4">
                    <div class="card">
                        <div class="card-header p-0 position-relative mt-n4 mx-3">
                            <div class="bg-orange shadow-orange radius-lg pt-3 pb-2">
                                <h4 class="text-white fw-bold ps-3">Labels</h4>
                            </div>
                        </div>
                        <div class="card-body px-2 pb-2">
                            <div class="table-responsive">
                                <table id="label" class="table table-sm">
                                    <thead>
                  	                    <tr>
                  	                        <th></th>
                  	                            <th colspan="3" class="text-center" style="background-color: #FAFAFA">'.(date('Y')-1).'</th>
                  	                            <th colspan="3" class="text-center">'.date('Y').'</th>
                  	                    </tr>
                                        <tr>
                                            <th></th>
                                            <th class="text-center text-secondary fw-bold" style="background-color: #FAFAFA">Press 1</th>
                                            <th class="text-center text-secondary fw-bold" style="background-color: #FAFAFA">Press 2</th>
                                            <th class="text-center text-secondary fw-bold" style="background-color: #FAFAFA">Press 2</th>
                                            <th class="text-center text-secondary fw-bold">Press 1</th>
                                            <th class="text-center text-secondary fw-bold">Press 2</th>
                                            <th class="text-center text-secondary fw-bold">Press 3</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="tabla3" class="row mt-5" style="display: none;">
                <div class="mb-4">
                    <div class="card">
                        <div class="card-header p-0 position-relative mt-n4 mx-3">
                            <div class="bg-orange shadow-orange radius-lg pt-3 pb-2">
                                <h4 class="text-white fw-bold ps-3">Treatments</h4>
                            </div>
                        </div>
                        <div class="card-body px-2 pb-2">
                            <div class="table-responsive">
                                <table id="treatment" class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th class="text-center text-secondary fw-bold">'.(date('Y')-1).'</th>
                                            <th class="text-center text-secondary fw-bold">'.date('Y').'</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="tabla4" class="row mt-5" style="display: none;">
                <div class="mb-4">
                    <div class="card">
                        <div class="card-header p-0 position-relative mt-n4 mx-3">
                            <div class="bg-orange shadow-orange radius-lg pt-3 pb-2">
                                <h4 class="text-white fw-bold ps-3">Embroidery</h4>
                            </div>
                        </div>
                        <div class="card-body px-2 pb-2">
                            <div class="table-responsive">
                                <table id="embroidery" class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th class="text-center text-secondary fw-bold">'.(date('Y')-1).'</th>
                                            <th class="text-center text-secondary fw-bold">'.date('Y').'</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="tabla5" class="row mt-5" style="display: none;">
                <div class="mb-4">
                    <div class="card">
                        <div class="card-header p-0 position-relative mt-n4 mx-3">
                            <div class="bg-orange shadow-orange radius-lg pt-3 pb-2">
                                <h4 class="text-white fw-bold ps-3">Shipped</h4>
                            </div>
                        </div>
                        <div class="card-body px-2 pb-2">
                            <div class="table-responsive">
                                <table id="shipped" class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th class="text-center text-secondary fw-bold">'.(date('Y')-1).'</th>
                                            <th class="text-center text-secondary fw-bold">'.date('Y').'</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="tabla6" class="row mt-5" style="display: none;">
                <div class="mb-4">
                    <div class="card">
                        <div class="card-header p-0 position-relative mt-n4 mx-3">
                            <div class="bg-orange shadow-orange radius-lg pt-3 pb-2">
                                <h4 class="text-white fw-bold ps-3">Orders Received</h4>
                            </div>
                        </div>
                        <div class="card-body px-2 pb-2">
                            <div class="table-responsive">
                                <table id="received" class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th class="text-center text-uppercase text-secondary fw-bold">'.(date('Y')-1).'</th>
                                            <th class="text-center text-uppercase text-secondary fw-bold">'.date('Y').'</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <script src="../assets/js/reportes.js"></script>';
    $code = ['html'=>$html];

    echo json_encode($code);
?>