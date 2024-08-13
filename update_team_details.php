<?php
session_start();
require_once 'db.php';

// Ensure the user is logged in and has the correct role
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'client') {
    header('Location: index.php');
    exit();
}

// Check if the team_id and update_fields are set
if (!isset($_POST['team_id']) || !is_numeric($_POST['team_id'])) {
    header('Location: view_registered_teams.php');
    exit();
}

$team_id = intval($_POST['team_id']);
$update_fields = isset($_POST['update_fields']) ? $_POST['update_fields'] : [];

// Prepare the update query
$update_query = "UPDATE teams SET ";
$update_params = [];
$update_types = "";

if (in_array("team_name", $update_fields)) {
    $update_query .= "team_name = ?, ";
    $update_params[] = $_POST['team_name'];
    $update_types .= "s";
}

if (in_array("leader_name", $update_fields)) {
    $update_query .= "leader_name = ?, ";
    $update_params[] = $_POST['leader_name'];
    $update_types .= "s";
}

if (in_array("leader_contact", $update_fields)) {
    $update_query .= "leader_contact = ?, ";
    $update_params[] = $_POST['leader_contact'];
    $update_types .= "s";
}

if (in_array("members", $update_fields)) {
    $update_query .= "members = ?, ";
    $update_params[] = $_POST['members'];
    $update_types .= "s";
}

// Remove the trailing comma and space
$update_query = rtrim($update_query, ", ");
$update_query .= " WHERE team_id = ? AND registered_by = ?";

$update_params[] = $team_id;
$update_params[] = $_SESSION['user_id'];
$update_types .= "ii";

// Execute the update query
$stmt = $conn->prepare($update_query);
$stmt->bind_param($update_types, ...$update_params);
$result = $stmt->execute();

if ($result) {
    header('Location: view_registered_teams.php?status=success');
} else {
    header('Location: view_registered_teams.php?status=error');
}

$stmt->close();
$conn->close();
?>
