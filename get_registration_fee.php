<?php
require_once 'db.php';

if (isset($_GET['tournament_id'])) {
    $tournament_id = intval($_GET['tournament_id']);
    $query = "SELECT registration_fee FROM tournaments WHERE tournament_id = ?";
    
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("i", $tournament_id);
        $stmt->execute();
        $stmt->bind_result($registration_fee);
        $stmt->fetch();
        $stmt->close();

        echo json_encode(['registration_fee' => $registration_fee]);
    } else {
        echo json_encode(['error' => 'Error preparing query: ' . $conn->error]);
    }
}

$conn->close();
?>
