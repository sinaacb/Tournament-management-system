<?php
require_once 'db.php';

if (isset($_GET['tournament_id'])) {
    $tournament_id = intval($_GET['tournament_id']);
    $query = "SELECT name, sport, gender, category, date, type, location, organizer_name, contact_no, registration_deadline, registration_fee FROM tournaments WHERE tournament_id = ?";
    
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("i", $tournament_id);
        $stmt->execute();
        $stmt->bind_result($name, $sport, $gender, $category, $date, $type, $location, $organizer_name, $contact_no, $registration_deadline, $registration_fee);
        $stmt->fetch();
        $stmt->close();

        echo json_encode([
            'name' => $name,
            'sport' => $sport,
            'gender' => $gender,
            'category' => $category,
            'date' => $date,
            'type' => $type,
            'location' => $location,
            'organizer_name' => $organizer_name,
            'contact_no' => $contact_no,
            'registration_deadline' => $registration_deadline,
            'registration_fee' => $registration_fee
        ]);
    } else {
        echo json_encode(['error' => 'Error preparing query: ' . $conn->error]);
    }
}

$conn->close();
?>
