<!DOCTYPE html>
<?php
session_start();

if (isset($_SESSION['username'])) {
    header('location: pages/home.php');
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
    <link href="assets/font/FontAwesome6/css/fontawesome6.2.css" rel="stylesheet">
    <link href="assets/font/FontAwesome6/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/simple-notify@1.0.4/dist/simple-notify.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/simple-notify@1.0.4/dist/simple-notify.css">
    <link href="https://fonts.googleapis.com/css2?family=Archivo+Black&family=Noto+Sans:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/sign-in.css">
</head>
<body>
    <div class="container-sign">
        <div class="logo-container">
            <span class="text-white mt-4 mb-4">Welcome to Demo</span>
        </div>
        <div class="form-container">
            <div class="d-flex flex-column mt-3">
                <label for="username">Username</label>
                <input type="text" id="user" name="username" class="input-user" maxlength="25">
                <label for="password">Password</label>
                <div class="input-pass">
                    <input type="password" id="pass" name="password" class="no-border w-215" maxlength="25">
                    <i class="fa-regular fa-eye-slash cursor-pointer" id="showPass"></i>
                </div>
                <div class="terms">
                    <input type="checkbox" id="rememberMe" checked>
                    <label for="rememberMe">Remember me</label>
                </div>
                <button type="submit" id="sign" class="signup-btn" onclick="sendData()">Sign In</button>
            </div>
            <div class="mt-3 d-flex flex-column">
                <button type="button" id="demo" class="demo-btn" onclick="demo()">Demo</button>
            </div>
        </div>
        <div class="image-container">
            <h1 class="title text-white">Welcome to Demo</h1>
            <p class="text-white mb-15">We're excited to have you here. Let's get started.</p>
        </div>
    </div>
    <div id="changePass" class="modal fade" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="changePassLabel" class="modal-title fs-5 fw-bold">Please Change Password</h5>
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
                    <button type="submit" id="enviar" class="btn btn-sm btn-primary fw-bold" onclick="changes()">OK</button>
                    <button type="button" id="cancel2" class="btn btn-secondary btn-sm fw-bold" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    <div id="confirm" class="modal fade" tabindex="-1" aria-labelledby="confirmPassLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="confirmPassLabel" class="modal-title fs-5 fw-bold">Confirm Changes</h5>
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
    <script src="assets/js/sign.js"></script>
</body>
</html>