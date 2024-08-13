<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'client') {
    header('Location: index.php');
    exit();
}

// Fetch approved tournaments for the logged-in user
$user_id = $_SESSION['user_id'];
$stmt_approved = $conn->prepare("SELECT tournament_id, name, sport, date, fixtures FROM tournaments WHERE created_by = ? AND status = 'approved' AND deleted = FALSE");
$stmt_approved->bind_param("i", $user_id);
$stmt_approved->execute();
$result_approved = $stmt_approved->get_result();
$tournaments_approved = $result_approved->fetch_all(MYSQLI_ASSOC);
$stmt_approved->close();

// Fetch pending tournaments for the logged-in user
$stmt_pending = $conn->prepare("SELECT tournament_id, name, sport, date, fixtures FROM tournaments WHERE created_by = ? AND status = 'pending' AND deleted = FALSE");
$stmt_pending->bind_param("i", $user_id);
$stmt_pending->execute();
$result_pending = $stmt_pending->get_result();
$tournaments_pending = $result_pending->fetch_all(MYSQLI_ASSOC);
$stmt_pending->close();

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Registered Tournaments</title>
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
            width: 1050px;
            margin: 20px auto;
            margin-left: 280px;
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

        .go-back {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <?php include 'client_sidebar.php'; ?>
    <div class="container">
        <h2>Tournaments Approved</h2>
        <?php if (count($tournaments_approved) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Sport</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tournaments_approved as $tournament): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($tournament['tournament_id']); ?></td>
                            <td><?php echo htmlspecialchars($tournament['name']); ?></td>
                            <td><?php echo htmlspecialchars($tournament['sport']); ?></td>
                            <td><?php echo htmlspecialchars($tournament['date']); ?></td>
                            <td>
                                <a class="btn" href="view_details_my.php?tournament_id=<?php echo htmlspecialchars($tournament['tournament_id']); ?>">Show Details</a>
                                <a class="btn" href="javascript:void(0);" onclick="confirmUpload('<?php echo htmlspecialchars($tournament['fixtures'] ?? ''); ?>', <?php echo htmlspecialchars($tournament['tournament_id']); ?>);">Upload Fixtures</a>
                                <a class="btn" href="remove_tournament.php?tournament_id=<?php echo htmlspecialchars($tournament['tournament_id']); ?>">Remove Tournament</a>
                                <a class="btn" href="payment_details_client.php?tournament_id=<?php echo htmlspecialchars($tournament['tournament_id']); ?>">Settle Payments</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No approved tournaments registered.</p>
        <?php endif; ?>

        <h2>Tournaments with Pending Status</h2>
        <?php if (count($tournaments_pending) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Sport</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tournaments_pending as $tournament): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($tournament['tournament_id']); ?></td>
                            <td><?php echo htmlspecialchars($tournament['name']); ?></td>
                            <td><?php echo htmlspecialchars($tournament['sport']); ?></td>
                            <td><?php echo htmlspecialchars($tournament['date']); ?></td>
                            <td>
                                <a class="btn" href="view_details.php?tournament_id=<?php echo htmlspecialchars($tournament['tournament_id']); ?>">Show Details</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No pending tournaments registered.</p>
        <?php endif; ?>

        <div class="go-back">
            <form action="client.php" method="get">
                <button type="submit" class="btn">Go Back</button>
            </form>
        </div>
    </div>

    <script>
        function confirmUpload(fixtures, tournamentId) {
            if (fixtures) {
                if (confirm("A fixtures file already exists. Do you want to replace it?")) {
                    window.location.href = 'upload_fixtures.php?tournament_id=' + tournamentId;
                }
            } else {
                window.location.href = 'upload_fixtures.php?tournament_id=' + tournamentId;
            }
        }
    </script>
</body>
</html>
