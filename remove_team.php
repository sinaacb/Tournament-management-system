<?php
session_start();
require_once 'db.php';

// Ensure the user is logged in and has the correct role
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'client') {
    header('Location: index.php');
    exit();
}

if (isset($_GET['team_id'])) {
    $team_id = $_GET['team_id'];

    // Fetch the registration fee and tournament ID
    $stmt = $conn->prepare("SELECT tournament_id FROM teams WHERE team_id = ?");
    $stmt->bind_param("i", $team_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $team = $result->fetch_assoc();
    $stmt->close();

    if ($team) {
        $tournament_id = $team['tournament_id'];

        // Get the registration fee
        $stmt = $conn->prepare("SELECT registration_fee FROM tournaments WHERE tournament_id = ?");
        $stmt->bind_param("i", $tournament_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $tournament = $result->fetch_assoc();
        $registration_fee = $tournament['registration_fee'];
        $stmt->close();

        // Calculate the refund amount (80% of registration fee)
        $refund_amount = 0.8 * $registration_fee;

        // Remove the team
        $stmt = $conn->prepare("DELETE FROM teams WHERE team_id = ?");
        $stmt->bind_param("i", $team_id);
        $stmt->execute();
        $stmt->close();


        // Redirect to the registered teams page
        header('Location: view_registered_teams.php');
        exit();
    } else {
        echo "Team not found.";
    }
} else {
    echo "No team ID provided.";
}

$conn->close();
?>
