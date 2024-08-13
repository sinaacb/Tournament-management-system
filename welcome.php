<?php
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit();
}

if ($_SESSION['role'] == 'admin') {
    header('Location: admin.php');
    exit();
} else {
    header('Location: client.php');
    exit();
}
