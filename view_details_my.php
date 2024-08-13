<?php
session_start();
require_once 'db.php';

$tournament = null;
$teams = [];

if (isset($_GET['tournament_id'])) {
    $tournament_id = intval($_GET['tournament_id']); // Ensure the ID is an integer

    // Fetch tournament details
    $query = "SELECT * FROM tournaments WHERE tournament_id = ?";
    
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("i", $tournament_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $tournament = $result->fetch_assoc();
        $stmt->close();
    } else {
        echo "Error preparing query: " . $conn->error;
        exit();
    }

    // Fetch registered teams
    $queryTeams = "SELECT * FROM teams WHERE tournament_id = ?";
    
    if ($stmtTeams = $conn->prepare($queryTeams)) {
        $stmtTeams->bind_param("i", $tournament_id);
        $stmtTeams->execute();
        $resultTeams = $stmtTeams->get_result();
        
        while ($row = $resultTeams->fetch_assoc()) {
            $teams[] = $row;
        }
        
        $stmtTeams->close();
    } else {
        echo "Error preparing teams query: " . $conn->error;
        exit();
    }
} else {
    echo "No tournament ID provided.";
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tournament Details</title>
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
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
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

        a {
            color: #007bff;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
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

        button {
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            padding: 10px 20px;
            margin-top: 10px;
        }

        button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <?php include 'client_sidebar.php'; ?>
    <div class="container">
        <h2>Tournament Details</h2>
        <?php if ($tournament): ?>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($tournament['name']); ?></p>
            <p><strong>Sport:</strong> <?php echo htmlspecialchars($tournament['sport']); ?></p>
            <p><strong>Gender:</strong> <?php echo htmlspecialchars($tournament['gender']); ?></p>
            <p><strong>Category:</strong> <?php echo htmlspecialchars($tournament['category']); ?></p>
            <p><strong>Date:</strong> <?php echo htmlspecialchars($tournament['date']); ?></p>
            <p><strong>Type:</strong> <?php echo htmlspecialchars($tournament['type']); ?></p>
            <p><strong>Location:</strong> <?php echo htmlspecialchars($tournament['location']); ?></p>
            <p><strong>Organizer Name:</strong> <?php echo htmlspecialchars($tournament['organizer_name']); ?></p>
            <p><strong>Contact No:</strong> <?php echo htmlspecialchars($tournament['contact_no']); ?></p>
            <p><strong>Registration Deadline:</strong> <?php echo htmlspecialchars($tournament['registration_deadline']); ?></p>
            <p><strong>Registration Fee:</strong> <?php echo htmlspecialchars($tournament['registration_fee']); ?></p>
            <p><strong>Location Link:</strong> <a href="<?php echo htmlspecialchars($tournament['location_link']); ?>" target="_blank"><?php echo htmlspecialchars($tournament['location_link']); ?></a></p>

            <?php
            if (!empty($tournament['details'])) {
                // Assuming 'details' contains the filename
                $fileName = '/tournament/' . $tournament['details'];
                // Generate a path to the file
                $filePath = $_SERVER['DOCUMENT_ROOT'] . $fileName;

                if (file_exists($filePath)) {
                    echo '<a href="' . $fileName . '" target="_blank">Show Details</a>';
                } else {
                    echo "<p>Details file not found.</p>";
                }
                echo '<br>'; // Break line between links
            } else {
                echo "<p>No details available.</p>";
            }
            ?>

            <?php
            if (!empty($tournament['fixtures'])) {
                // Assuming 'fixtures' contains the filename
                $fixtureFileName = '/tournament/' . $tournament['fixtures'];
                // Generate a path to the file
                $fixtureFilePath = $_SERVER['DOCUMENT_ROOT'] . $fixtureFileName;

                if (file_exists($fixtureFilePath)) {
                    echo '<a href="' . $fixtureFileName . '" target="_blank">Show Fixtures</a>';
                } else {
                    echo "<p>Fixture file not found.</p>";
                }
            } else {
                echo "<p>No fixtures available.</p>";
            }
            ?>
        <?php else: ?>
            <p>Tournament not found.</p>
        <?php endif; ?>

        <?php if (count($teams) > 0): ?>
            <h2>Registered Teams</h2>
            <table>
                <thead>
                    <tr>
                        <th>Team ID</th>
                        <th>Team Name</th>
                        <th>Captain Name</th>
                        <th>Captain Contact</th>
                        <th>Members</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($teams as $team): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($team['team_id']); ?></td>
                            <td><?php echo htmlspecialchars($team['team_name']); ?></td>
                            <td><?php echo htmlspecialchars($team['leader_name']); ?></td>
                            <td><?php echo htmlspecialchars($team['leader_contact']); ?></td>
                            <td><?php echo htmlspecialchars($team['members']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No teams registered for this tournament.</p>
        <?php endif; ?>

        <!-- Go Back Button -->
        <a href="view_registered_tournaments.php">
            <button>Go Back</button>
        </a>
    </div>
</body>
</html>
