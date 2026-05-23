<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
    content="width=device-width, initial-scale=1.0">

    <title>
        Booking Lapangan Futsal
    </title>

    <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet">

    <link rel="stylesheet"
    href="assets/css/style.css">

</head>

<body class="bg-light">

<!-- NAVBAR -->

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow">

    <div class="container">

        <a class="navbar-brand fw-bold"
        href="index.php">

            Booking Futsal

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
                    href="index.php?p=booking">

                        Booking

                    </a>

                </li>

                <li class="nav-item">

                    <a class="nav-link"
                    href="index.php?p=jadwal">

                        Jadwal

                    </a>

                </li>

                <li class="nav-item">

                    <a class="nav-link"
                    href="index.php?p=about">

                        About

                    </a>

                </li>

                <li class="nav-item">

                    <a class="nav-link"
                    href="index.php?p=contact">

                        Contact

                    </a>

                </li>

                <li class="nav-item">

                    <a class="btn btn-success ms-2"
                    href="index.php?p=loginadmin">

                        Admin

                    </a>

                </li>

            </ul>

        </div>

    </div>

</nav>

<!-- CONTENT -->

<div class="container-fluid p-0">

<?php

$pages_dir = 'pages';

if(!empty($_GET['p'])){

    $p = $_GET['p'];

    include($pages_dir.'/'.$p.'.php');

} else {

    include($pages_dir.'/home.php');

}

?>

</div>

<!-- FOOTER -->

<footer class="bg-dark text-white text-center p-4 mt-5">

    <p class="mb-0">

        © 2026 Sistem Booking Lapangan Futsal ferdi. All rights reserved.

    </p>

</footer>

</body>
</html>