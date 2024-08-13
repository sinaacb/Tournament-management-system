<?php
session_start();
require_once 'db.php';

// Ensure the user is logged in and has the correct role
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'client') {
    header('Location: index.php');
    exit();
}

// Check if the team_id is provided
if (!isset($_GET['team_id'])) {
    echo "No team ID provided.";
    exit();
}

$team_id = intval($_GET['team_id']); // Ensure the ID is an integer

// Fetch team details and tournament name
$stmt = $conn->prepare("SELECT teams.*, tournaments.name AS tournament_name FROM teams JOIN tournaments ON teams.tournament_id = tournaments.tournament_id WHERE team_id = ?");
$stmt->bind_param("i", $team_id);
$stmt->execute();
$result = $stmt->get_result();
$team = $result->fetch_assoc();
$stmt->close();

// Check if the team exists
if (!$team) {
    echo "Team not found.";
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Team Details</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
        }
        .sidebar {
            width: 200px;
            background-color: #333;
            padding: 20px;
            position: fixed;
            height: 100%;
        }

        .sidebar a {
            display: block;
            color: #fff;
            padding: 10px;
            text-decoration: none;
            margin-bottom: 10px;
            border-radius: 4px;
        }

        .sidebar a:hover {
            background-color: #575757;
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
            margin-top: 20px;
        }

        p {
            font-size: 16px;
            color: #555;
        }

        p strong {
            color: #333;
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
    <?php include 'client_sidebar.php'; ?>
    <div class="container">
        <h2>Team Details</h2>
        <?php if ($team): ?>
            <p><strong>Team Name:</strong> <?php echo htmlspecialchars($team['team_name']); ?></p>
            <p><strong>Captain Name:</strong> <?php echo htmlspecialchars($team['leader_name']); ?></p>
            <p><strong>Captain Contact:</strong> <?php echo htmlspecialchars($team['leader_contact']); ?></p>
            <p><strong>Members:</strong> <?php echo htmlspecialchars($team['members']); ?></p>
            <p><strong>Tournament Name:</strong> <?php echo htmlspecialchars($team['tournament_name']); ?></p>
            <a class="btn" href="view_details.php?tournament_id=<?php echo htmlspecialchars($team['tournament_id']); ?>">View Tournament Details</a>
        <?php else: ?>
            <p>Team not found.</p>
        <?php endif; ?>
        <a class="btn" href="view_registered_teams.php">Back to Registered Teams</a>
    </div>
</body>
</html>
