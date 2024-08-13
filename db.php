<?php
$conn = mysqli_connect('localhost', 'root', 'password', 'TMS');

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
