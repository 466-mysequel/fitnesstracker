<?php
$start = hrtime(true);
ob_start("ob_gzhandler"); // enable gzip compression on output
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include_once '../src/library.php';
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <!-- Bootstrap: -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
html {
    position: relative;
    min-height: 100%;
}
body {
    margin-bottom: 60px;
}
.footer {
    position: absolute;
    bottom: 0;
    width: 100%;
    height: 55px;
    line-height: 50px;
    background-color: #f5f5f5;
}
<?php if(isset($style)) echo $style ?>
    </style>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>
<body>
    <header>
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark static-top">
            <div class="container">
                <a class="navbar-brand" href="#">Start Tracking!</a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarResponsive">
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item">
                            <a class="nav-link<?php if(strpos($_SERVER['REQUEST_URI'], "/index.php") !== false) echo " active" ?>" title="Home" alt="Home" href="index.php">Home</a>
                        </li>
                        <?php if(is_authenticated()): ?>
                        <li class="nav-item">
                            <a class="nav-link<?php if(strpos($_SERVER['REQUEST_URI'], "/myaccount.php") !== false) echo " current" ?>" title="My account" alt="My account" href="account.php">My account</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link<?php if(strpos($_SERVER['REQUEST_URI'], "/logout.php") !== false) echo " current" ?>" title="Sign out" alt="Sign out" href="logout.php">Sign out</a>
                        </li>
                        <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link<?php if(strpos($_SERVER['REQUEST_URI'], "/signup.php") !== false) echo " current" ?>" title="Create account" alt="Create account" href="signup.php">Create account</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link<?php if(strpos($_SERVER['REQUEST_URI'], "/login.php") !== false) echo " current" ?>" title="Sign in" alt="Sign in" href="login.php">Sign in</a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
    </nav>
   </header>
