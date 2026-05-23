<?php
session_start();

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

/* akun admin */
$admin_email = "admin@gmail.com";
$admin_password = "admin123";

if (
    $email === $admin_email &&
    $password === $admin_password
) {

    $_SESSION['admin'] = true;

    header("Location: ../index.php?p=dashboardadmin");
    exit();

} else {

    echo "
    <script>
        alert('Email atau Password salah!');
        window.location.href='../index.php?p=loginadmin';
    </script>
    ";
}
?>