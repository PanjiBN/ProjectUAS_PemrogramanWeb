<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
    content="width=device-width, initial-scale=1.0">

    <title>
        Booking Lapangan
    </title>

    <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet">

</head>

<body>

<!-- NAVBAR -->

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">

    <div class="container">

        <a class="navbar-brand"
        href="index.php">

            Booking Lapangan

        </a>

        <button class="navbar-toggler"
        type="button"
        data-bs-toggle="collapse"
        data-bs-target="#navbarNav">

            <span class="navbar-toggler-icon"></span>

        </button>

        <div class="collapse navbar-collapse"
        id="navbarNav">

            <ul class="navbar-nav ms-auto">

                <li class="nav-item">

                    <a class="nav-link"
                    href="index.php">

                        Home

                    </a>

                </li>

                <li class="nav-item">

                    <a class="nav-link"
                    href="index.php?p=about">

                        About

                    </a>

                </li>

            </ul>

        </div>

    </div>

</nav>

<!-- CONTENT -->

<div class="container mt-5">

<?php

$pages_dir = 'pages';

if(!empty($_GET['p'])){

    $p = $_GET['p'];

    include($pages_dir.'/'.$p.'.php');

} else {

?>

    <div class="text-center">

        <h1 class="text-success">

            Selamat Datang

        </h1>

        <p>

            Sistem Booking Lapangan Berbasis Web

        </p>

    </div>

<?php

}

?>

</div>

</body>
</html>