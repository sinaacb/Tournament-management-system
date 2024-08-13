<?php
session_start();
require_once 'db.php';

// Ensure the user is logged in and has the correct role
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'client') {
    header('Location: index.php');
    exit();
}

// Fetch registered teams for the logged-in user
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT team_id, team_name, tournament_id, leader_name, leader_contact, confirmed FROM teams WHERE registered_by = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Check if the user has registered any teams
if ($result->num_rows > 0) {
    $teams = [];
    while ($row = $result->fetch_assoc()) {
        $teams[] = $row;
    }
} else {
    $teams = [];
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Registered Teams</title>
    <style>
        /* Your existing styles */
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
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #007bff;
            color: #fff;
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

        .btn-remove {
            background-color: #dc3545;
        }

        .btn-remove:hover {
            background-color: #c82333;
        }

        .btn-confirm {
            background-color: #28a745;
        }

        .btn-confirm:hover {
            background-color: #218838;
        }

        .go-back-btn {
            display: block;
            width: 150px;
            margin: 20px auto;
            padding: 10px 20px;
            color: #fff;
            background-color: #007bff;
            text-decoration: none;
            border-radius: 4px;
            text-align: center;
        }

        .go-back-btn:hover {
            background-color: #0056b3;
        }
    </style>
    <script>
        function confirmRemove(teamId) {
            if (confirm('Are you sure you want to remove this team? You will get only 80% of the registration fee.')) {
                window.location.href = 'remove_team.php?team_id=' + teamId;
            }
        }

        function confirmTeam(teamId, tournamentId) {
            if (confirm('Are you sure you want to confirm this team?')) {
                window.location.href = 'confirm_team.php?team_id=' + teamId + '&tournament_id=' + tournamentId;
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <h2>Registered Teams</h2>
        <?php if (count($teams) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Team Name</th>
                        <th>Tournament ID</th>
                        <th>Captain Name</th>
                        <th>Captain Contact</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($teams as $team): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($team['team_id']); ?></td>
                            <td><?php echo htmlspecialchars($team['team_name']); ?></td>
                            <td><?php echo htmlspecialchars($team['tournament_id']); ?></td>
                            <td><?php echo htmlspecialchars($team['leader_name']); ?></td>
                            <td><?php echo htmlspecialchars($team['leader_contact']); ?></td>
                            <td>
                                <a class="btn" href="view_team_details.php?team_id=<?php echo htmlspecialchars($team['team_id']); ?>">View Details</a>
                                <a class="btn" href="edit_team_details.php?team_id=<?php echo htmlspecialchars($team['team_id']); ?>">Edit Details</a>
                                <a class="btn btn-remove" href="javascript:void(0);" onclick="confirmRemove(<?php echo htmlspecialchars($team['team_id']); ?>)">Remove Team</a>
                                <?php if ($team['confirmed'] == 0): ?>
                                    <a class="btn btn-confirm" href="javascript:void(0);" onclick="confirmTeam(<?php echo htmlspecialchars($team['team_id']); ?>, <?php echo htmlspecialchars($team['tournament_id']); ?>)">Confirm</a>
                                <?php else: ?>
                                    <span>Confirmed</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No teams registered.</p>
        <?php endif; ?>
        <!-- Go Back Button -->
        <a href="client.php" class="go-back-btn">Go Back</a>
    </div>
</body>
</html>
