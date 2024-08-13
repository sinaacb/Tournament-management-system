<?php
require_once 'db.php';

$query = "SELECT DISTINCT sport FROM tournaments WHERE status = 'approved' AND deleted = 0";
$result = mysqli_query($conn, $query);

$sports = [];
while ($row = mysqli_fetch_assoc($result)) {
    $sports[] = ['id' => $row['sport'], 'text' => $row['sport']];
}

echo json_encode($sports);

mysqli_close($conn);
?>
