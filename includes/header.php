<?php require_once __DIR__ . '/helpers.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>RichCare Hospital</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/site.css">
</head>
<body>
<nav class="navbar navbar-default navbar-fixed-top richcare-nav">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#main-nav">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand logo-brand" href="index.php">
                <img src="assets/img/richcare-logo.svg" alt="RichCare Hospital">
            </a>
        </div>
        <div class="collapse navbar-collapse" id="main-nav">
            <ul class="nav navbar-nav navbar-right">
                <li><a data-page="home" href="index.php">Home</a></li>
                <li><a data-page="about" href="index.php#about">About</a></li>
                <li><a data-page="services" href="index.php#services">Services</a></li>
                <li><a data-page="book" href="book.php">Book</a></li>
                <li><a data-page="contact" href="index.php#contact">Contact</a></li>
                <li class="dropdown">
                    <a class="dropdown-toggle emergency-link" href="#" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                        <span class="glyphicon glyphicon-earphone"></span> Ghana Emergency <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu emergency-menu">
                        <li><a href="tel:112"><strong>112</strong> General emergency</a></li>
                        <li><a href="tel:193"><strong>193</strong> Ambulance</a></li>
                        <li><a href="tel:191"><strong>191</strong> Police</a></li>
                        <li><a href="tel:192"><strong>192</strong> Fire Service</a></li>
                    </ul>
                </li>
                <li><a class="staff-link" data-page="staff" href="login.php">Staff Portal</a></li>
            </ul>
        </div>
    </div>
</nav>
