<?php
require_once 'db.php';

$firstname = $_POST['firstname'];
$lastname = $_POST['lastname'];
$contact_number = $_POST['contact_number'];
$email = $_POST['email'];
$username = $_POST['username'];
$password = $_POST['password'];

// Check if username already exists
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    // Username exists
    header('Location: signup.php?error=Username already exists. Please choose another username.');
    $stmt->close();
    $conn->close();
    exit();
}

$stmt->close();

// Proceed with registration
$stmt = $conn->prepare("INSERT INTO users (firstname, lastname, contact_number, email, username, password, role) VALUES (?, ?, ?, ?, ?, ?, 'client')");
$stmt->bind_param("ssssss", $firstname, $lastname, $contact_number, $email, $username, $password);

if ($stmt->execute()) {
    header('Location: index.php');
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
