<?php
session_start();
require_once 'db.php';

// Ensure the user is logged in and has the correct role
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'client') {
    header('Location: index.php');
    exit();
}

// Ensure team_id and tournament_id are set
if (isset($_GET['team_id']) && isset($_GET['tournament_id'])) {
    $team_id = $_GET['team_id'];
    $tournament_id = $_GET['tournament_id'];

    // Begin transaction
    $conn->begin_transaction();

    try {
        // Increment the confirmed_teams column for the respective tournament
        $stmt = $conn->prepare("UPDATE tournaments SET confirmed_teams = confirmed_teams + 1 WHERE tournament_id = ?");
        $stmt->bind_param("i", $tournament_id);
        $stmt->execute();
        $stmt->close();

        // Set the confirmed column to 1 for the respective team
        $stmt = $conn->prepare("UPDATE teams SET confirmed = 1 WHERE team_id = ?");
        $stmt->bind_param("i", $team_id);
        $stmt->execute();
        $stmt->close();

        // Commit transaction
        $conn->commit();

        // Success message
        $_SESSION['message'] = "Team confirmed successfully.";
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $_SESSION['message'] = "Error confirming team: " . $e->getMessage();
    }
} else {
    $_SESSION['message'] = "Invalid request.";
}

$conn->close();

// Redirect back to the registered teams page
header('Location: view_registered_teams.php');
exit();
?>
