<?php
require_once 'db.php';

$query = "SELECT DISTINCT name FROM tournaments WHERE status = 'approved' AND deleted = 0";
$result = mysqli_query($conn, $query);

$names = [];
while ($row = mysqli_fetch_assoc($result)) {
    $names[] = ['id' => $row['name'], 'text' => $row['name']];
}

echo json_encode($names);

mysqli_close($conn);
?>
