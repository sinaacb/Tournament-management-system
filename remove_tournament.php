<?php
session_start();
require_once 'db.php';

// Ensure the user is logged in and has the correct role
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'client') {
    header('Location: index.php');
    exit();
}

// Check if tournament_id is provided
if (isset($_GET['tournament_id'])) {
    $tournament_id = intval($_GET['tournament_id']); // Ensure the ID is an integer
    $user_id = $_SESSION['user_id']; // Get the user ID from the session

    // Check if there are any registered teams for the tournament
    $checkTeamsQuery = "SELECT COUNT(*) as team_count FROM teams WHERE tournament_id = ?";
    if ($stmt = $conn->prepare($checkTeamsQuery)) {
        $stmt->bind_param("i", $tournament_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $team_count = $row['team_count'];
        $stmt->close();

        if ($team_count > 0) {
            $message = "The tournament cannot be deleted because it already has registered teams.";
        } else {
            // Prepare the SQL query to soft delete the tournament
            $deleteQuery = "UPDATE tournaments SET deleted = TRUE WHERE tournament_id = ? AND created_by = ?";
            if ($stmt = $conn->prepare($deleteQuery)) {
                $stmt->bind_param("ii", $tournament_id, $user_id);
                if ($stmt->execute()) {
                    // Check if a row was affected
                    if ($stmt->affected_rows > 0) {
                        $message = "Tournament removed successfully.";
                    } else {
                        $message = "You are not authorized to remove this tournament or it does not exist.";
                    }
                } else {
                    $message = "Error executing query: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $message = "Error preparing query: " . $conn->error;
            }
        }
    } else {
        $message = "Error preparing check teams query: " . $conn->error;
    }
} else {
    $message = "No tournament ID provided.";
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Remove Tournament</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #333;
        }

        p {
            text-align: center;
            color: #007bff;
        }

        .btn {
            display: inline-block;
            padding: 8px 16px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            text-align: center;
        }

        .btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Remove Tournament</h2>
        <p><?php echo htmlspecialchars($message); ?></p>
        <p><a class="btn" href="view_registered_tournaments.php">Back to Registered Tournaments</a></p>
    </div>
</body>
</html>
