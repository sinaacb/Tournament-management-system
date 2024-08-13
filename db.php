<?php
$conn = mysqli_connect('localhost', 'root', 'Sinaaaan', 'TMS');

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
