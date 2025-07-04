<!DOCTYPE html>
<?php
session_start();
include '../src/models/dashboard.class.php';

$obj = new controlDB("prod","");
$res = new dashData();
$res->cardsData();
$data = parse_ini_file("../../env/.env");
$BD = $data['prefixC'];

$user = $_SESSION['username'];
$sql = "SELECT name, last_name, puesto, us_admin, production FROM {$BD}usuarios.usuarios INNER JOIN {$BD}usuarios.permission ON usuarios.id_usuario = permission.id_user WHERE username = :user";
$permiso = $obj->consultar($sql, [':user'=>$user]);
$production = $permiso[0]['production'];
$admin = $permiso[0]['us_admin'];
$name = ucwords($permiso[0]['name']);
$last = $permiso[0]['last_name'];
$fullName = ucwords($name." ".$last);
$position = ucwords($permiso[0]['puesto']);

if (!isset($_SESSION['username'])) {
    header("location: ../index.php");
} else if ($production == '1') {
    header("location: home_production.php");
} 
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demo Dashboard</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link href="../assets/font/FontAwesome6/css/fontawesome6.2.css" rel="stylesheet">
    <link href="../assets/font/FontAwesome6/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/simple-notify@1.0.4/dist/simple-notify.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/simple-notify@1.0.4/dist/simple-notify.css">
    <script src='https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.15/index.global.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@6.1.15/index.global.min.js'></script>
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/js/tom-select.complete.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.bootstrap5.css" rel="stylesheet">
    <script src="../assets/js/plugins/chartjs.min.js"></script>
    <script src="../assets/js/dashboard.js"></script>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <script src="../assets/js/app.js"></script>
    <script src="../assets/js/logOut.js"></script>
    <script src="../assets/js/statistics.js"></script>
</head>
<body>
    <aside class="sidenav navbar dark-mode bar radius-xl fixed-start" id="nBar">
        <div class="sidenav-header">
            <i class="fas fa-times p-3 text-white cursor-pointer opacity-50 position-absolute end-0 top-0 none" aria-hidden="true" id="closeIcon"></i>
            <a href="home.php">
                <img src="../assets/img/charly_creative.png" alt="logo" class="brand mt-3">
            </a>
        </div>
        <hr class="horizontal light mt-1 mb-2">
        <div class="w-auto navbar-collapse">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a href="#" id="active3" class="nav-link active" data-bs-toggle="collapse" data-bs-target="#menuDashboard">
                        <div class="me-2 d-flex">
                            <i class="fa-thin fa-table-layout fa-xl"></i>
                        </div>Dashboard
                    </a>
                </li>
                <ul id="menuDashboard" class="navbar-nav ms-4 collapse">
                    <li class="nav-item">
                        <a class="nav-link bg-orange" href="#" onclick="loadContent('Dashboard','dashboard')" data-page="dashboard">
                            <div class="me-2 d-flex">
                                <!-- <i class="fa-regular fa-money-bill-trend-up fa-xl"></i> -->
                            </div>Wip
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" onclick="loadContent('Dashboard','production')" data-page="production">
                            <div class="me-2 d-flex">
                                <!-- <i class="fa-regular fa-table-tree fa-xl"></i> -->
                            </div>Production
                        </a>
                    </li>
                </ul>
                <li class="nav-item">
                    <a class="nav-link" href="#" onclick="loadContent('capacity','capacity')" data-page="capacity">
                        <div class="me-2 d-flex">
                            <i class="fa-thin fa-calendar-days fa-xl"></i>
                        </div>Capacity
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" id="active" class="nav-link" data-bs-toggle="collapse" data-bs-target="#menuSales">
                        <div class="me-2 d-flex">
                            <i class="fa-thin fa-money-bill-1-wave fa-xl"></i>
                        </div>Sales
                    </a>
                </li>
                <ul id="menuSales" class="navbar-nav ms-4 collapse">
                    <li class="nav-item">
                        <a class="nav-link" href="#" onclick="loadContent('sales','goals')" data-page="goals">
                            <div class="me-2 d-flex">
                                <!-- <i class="fa-regular fa-money-bill-trend-up fa-xl"></i> -->
                            </div>Goals
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" onclick="loadContent('sales','clients')" data-page="clients">
                            <div class="me-2 d-flex">
                                <!-- <i class="fa-regular fa-table-tree fa-xl"></i> -->
                            </div>Clients
                        </a>
                    </li>
                </ul>
                <li class="nav-item">
                    <a href="#" class="nav-link" onclick="loadContent('reports','reports')" data-page="reports">
                        <div class="me-2 d-flex">
                            <i class="fa-thin fa-file-spreadsheet fa-xl"></i>
                        </div>Reports
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" id="active2" class="nav-link" data-bs-toggle="collapse" data-bs-target="#menuProduction">
                        <div class="me-2 d-flex">
                            <i class="fa-thin fa-industry-windows fa-xl"></i>
                        </div>Production
                    </a>
                </li>
                <ul id="menuProduction" class="navbar-nav ms-4 collapse">
                    <li class="nav-item">
                        <a class="nav-link" href="#" onclick="loadContent('production','charts')" data-page="charts">
                            <div class="me-2 d-flex">
                                <!-- <i class="fa-regular fa-chart-column fa-xl"></i> -->
                            </div>Charts
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" onclick="loadContent('production','tables')" data-page="tables">
                            <div class="me-2 d-flex">
                                <!-- <i class="fa-regular fa-table-tree fa-xl"></i> -->
                            </div>Tables
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" onclick="loadContent('production','prints')" data-page="prints">
                            <div class="me-2 d-flex">
                                <!-- <i class="fa-regular fa-palette fa-xl"></i> -->
                            </div>Printing
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" onclick="loadContent('production','label')" data-page="label">
                            <div class="me-2 d-flex">
                                <!-- <i class="fa-solid fa-tags fa-xl"></i> -->
                            </div>Pad print
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" onclick="loadContent('production','embroidery')" data-page="embroidery">
                            <div class="me-2 d-flex">
                                <!-- <i class="fa-regular fa-reel fa-xl"></i> -->
                            </div>Embroidery
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" onclick="loadContent('production','treatment')" data-page="treatment">
                            <div class="me-2 d-flex">
                                <!-- <i class="fa-solid fa-fill-drip fa-xl"></i> -->
                            </div>Treatment
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" onclick="loadContent('production','packing')" data-page="packing">
                            <div class="me-2 d-flex">
                                <!-- <i class="fa-regular fa-boxes-packing fa-xl"></i> -->
                            </div>Packing
                        </a>
                    </li>
                </ul>
                <li class="nav-item">
                    <a href="#" class="nav-link" onclick="loadContent('members','members')" data-page="members">
                        <div class="me-2 d-flex">
                            <i class="fa-thin fa-users fa-xl"></i>
                        </div>Members
                    </a>
                </li>
            </ul>
        </div>
    </aside>
    <main class="main-content position-relative max-height-vh-100 h-100">
        <div class="alert alert-warning text-center">
            <strong>Demo Notice:</strong> This application is running in demo mode. The data reflects the system's state as of <strong>June 25, 2025</strong>.
        </div>
        <nav class="navbar navbar-main px-0 mx-2 shadow-none radius-xl fixed-top" id="barBlur" data-scroll="true"> <!-- start nav -->
            <div class="container-fluid py-y px-3">
                <nav aria-label="breadcrumb" id="page">
                    <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
                        <li class="breadcrumb-item">
                            <a href="home.php" class="text-dark">
                                <i class="fad fa-house fa-md" title="Home"></i>
                            </a>
                        </li>
                        <li class="breadcrumb-item text-dark">Dashboard</li>
                    </ol>
                    <h6 class="fw-bold mb-0" aria-current="page">Dashboard</h6>
                </nav>
                <div class="mt-sm-0 justify-content-end" id="navbar">
                    <div class="navbar-nav justify-content-end">
                        <div class="modal-hov dropdown d-flex align-items-center">
                            <i class="fad fa-circle-user fa-2xl me-sm-1 fs-2 cursor-pointer" title="Profile" id="profile_menu" data-bs-toggle="dropdown" aria-expanded="false"></i>
                            <div class="dropdown-menu dropdown-profile dropdown-menu-end mr2">
                                <div class="card position-relative border-0" style="box-shadow: none;">
                                    <div class="card-body p-0 mb-2">
                                        <div class="d-flex pt-2 px-3 border-bottom">
                                            <div>
                                                <img src="../assets/img/logo_5.png" class="rounded-circle" alt="profile_image" style="width: 48px;">
                                            </div>
                                            <div class="flex-grow-1 mt-1 ms-2">
                                                <h6 class="fw-bold mb-0"><?=$fullName;?></h6>
                                                <p class="text-body text-sm"><?=$position;?></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="d-flex flex-column mb-3 mt-2">
                                            <div class="nav-item">
                                                <a href="#" class="nav-link px-3 text-dark p-0" data-bs-toggle="modal" data-bs-target="#changePass">
                                                    <i class="fa-regular fa-key fa-md me-2"></i>
                                                    <!-- <i class="las la-lg la-key me-2"></i> -->
                                                    <span>Change Password</span>
                                                </a>
                                            </div>
                                            <input type="hidden" id="admin" value="<?=$admin;?>">
                                            <?php if ($admin == '1') { ?>
                                            <div class="nav-item mt-2">
                                                <a href="#" class="nav-link px-3 text-dark p-0" data-bs-toggle="modal" data-bs-target="#registers">
                                                    <i class="fa-regular fa-address-book fa-md me-2"></i>
                                                    <!-- <i class="lar la-lg la-address-book me-2"></i> -->
                                                    <span>Sessions Registers</span>
                                                </a>
                                            </div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                    <div class="card-footer p-0 border-top">
                                        <div class="px-3">
                                            <button type="button" id="exit" class="btn btn-logOut btn-sm d-flex flex-center w-100 mt-3 mb-2" onclick="logOut()">
                                                <i class="fa-regular fa-arrow-right-from-bracket fa-md me-2"></i>
                                                Sign Out
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="nav-item d-xxl-none d-flex align-items-center">
                                <div class="nav-link text-body p-0 cursor-pointer sidenav-toggler-inner" id="toggleIcon">
                                    <i class="fa-regular fa-bars fa-lg show-toggle" id="toggleClose"></i>
                                    <i class="fa-regular fa-bars-sort fa-lg hide-toggle" id="toggleOpen"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </nav> <!-- end nav -->
        <div id="changePass" class="modal fade" tabindex="-1" aria-labelledby="changePassLabel" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 id="changePassLabel" class="modal-title fw-bold">Change Password</h5>
                        <button type="button" id="cancel1" class="btn-close" data-bs-dismiss="modal" aria-label="close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <div class="row">
                                <div class="col-10">
                                    <label for="password" class="form-label">New Password</label>
                                    <input type="password" id="newPass" class="form-control form-control-sm radius-lg" data-bs-toggle="popover" data-bs-placement="left" data-bs-trigger="focus" data-bs-title="Password requirements" data-bs-content="Password must contain at least 8 characters, including UPPER/lowercase and numbers." maxlength="25">
                                </div>
                                <div class="col-1 mt-4-2">
                                    <i class="fa-regular fa-eye-slash fa-md cursor-pointer" id="showPass1"></i>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-10">
                                    <label for="password" class="form-label mt-2">Confirm Password</label>
                                    <input type="password" id="confirmPass" class="form-control form-control-sm radius-lg" maxlength="25">
                                </div>
                            </div>    
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-sm btn-primary fw-bold" onclick="changes()">OK</button>
                        <button type="button" id="cancel2" class="btn btn-secondary btn-sm fw-bold" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
        <div id="confirm" class="modal fade" tabindex="-1" aria-labelledby="confirmPassLabel" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 id="confirmPassLabel" class="modal-title fw-bold">Confirm Changes</h5>
                        <button type="button" id="cancel3" class="btn-close" data-bs-dismiss="modal" aria-label="close"></button>
                    </div>
                    <div class="modal-body">
                        Do you want to save the changes?
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-sm btn-secondary fw-bold" data-bs-target="#changePass" data-bs-toggle="modal">Back</button>
                        <button type="submit" class="btn btn-sm btn-primary fw-bold" onclick="confirm()">Save Changes</button>
                        <button type="button" id="cancel4" class="btn btn-secondary btn-sm fw-bold" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
        <?php if ($admin == '1') { ?>
        <div id="registers" class="modal fade" tabindex="-1" aria-labelledby="registerLabel" aria-hidden="true">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 id="registerLabel" class="modal-title fw-bold">Sessions Registers</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table id="session_register" class="table compact hover">
                                <thead>
                                    <tr>
                                        <th class="text-center text-secondary">User</th>
                                        <th class="text-center text-secondary">Name</th>
                                        <th class="text-center text-secondary">Position</th>
                                        <th class="text-center text-secondary">Date</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm fw-bold" onclick="session_reload()">Update</button>
                        <button type="button" class="btn btn-sm btn-primary fw-bold" data-bs-dismiss="modal">Ok</button>
                    </div>
                </div>
            </div>
        </div>
        <?php } ?>
        <div class="container-fluid py-1" id="changes_data">
            <div class="d-flex flex-column h-100">
                <h1 class="col-sm-6 col-md-5 col-xl-12 fw-semibold">Hello, <?=$name;?></h1>
                <p class="text-dark fs-6">Here's a summary of your activity.</p>
            </div>
            <div class="row mt-3">
                <div class="col-sm-6 col-lg-3 mb-xl-0 mb-4">
                    <div class="card">
                        <div class="card p-3 pt-2">
                            <div class="icon-lg bg-orange shadow-orange text-center radius-xl mt-n4 position-absolute">
                                <i class="fa-solid fa-dollar-sign"></i>
                            </div>
                            <div class="text-end pt-1">
                                <p class="text-sm fst-italic mb-0">Today's Money</p>
                                <h4 id="tamount" class="mb-0 fw-bold"><?=$res->Tamount?></h4>
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
                                <h4 id="orders" class="mb-0 fw-bold"><?=$res->orders?></h4>
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
                                <h4 id="blanks" class="mb-0 fw-bold"><?=$res->blanks?></h4>
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
                                <h4 id="hits" class="mb-0 fw-bold"><?=$res->hits?></h4>
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
        </div>
        <button type="button" class="btn bg-orange btn-floating btn-lg" id="back_top">
            <i class="fa-regular fa-arrow-up text-white"></i>
        </button>
    </main>
    <script>
        $("#menuDashboard").toggleClass("show");
        $('#active3').attr('aria-expanded', 'true');
    </script>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/2.0.5/css/dataTables.dataTables.css">
    <script type="text/javascript" src="https://cdn.datatables.net/2.0.5/js/dataTables.js"></script>
</body>
</html>