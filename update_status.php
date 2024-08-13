<?php
session_start();
require_once 'db.php';

// Check if the user is logged in and is an admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Handle the approve, pending, and remove actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tournament_id = $_POST['tournament_id'];
    $action = $_POST['action'];

    if ($action === 'approve') {
        $status = 'approved';
        $stmt = $conn->prepare("UPDATE tournaments SET status = ? WHERE tournament_id = ?");
        if (!$stmt) {
            die('Prepare failed: ' . $conn->error);
        }
        $stmt->bind_param("si", $status, $tournament_id);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Tournament approved successfully.";
        } else {
            $_SESSION['message'] = "Error approving tournament: " . $stmt->error;
        }
        $stmt->close();
    } elseif ($action === 'pending') {
        $status = 'pending';
        $stmt = $conn->prepare("UPDATE tournaments SET status = ? WHERE tournament_id = ?");
        if (!$stmt) {
            die('Prepare failed: ' . $conn->error);
        }
        $stmt->bind_param("si", $status, $tournament_id);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Tournament status set to pending.";
        } else {
            $_SESSION['message'] = "Error setting tournament status to pending: " . $stmt->error;
        }
        $stmt->close();
    } elseif ($action === 'remove') {
        $stmt = $conn->prepare("UPDATE tournaments SET deleted = 1 WHERE tournament_id = ?");
        if (!$stmt) {
            die('Prepare failed: ' . $conn->error);
        }
        $stmt->bind_param("i", $tournament_id);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Tournament removed successfully.";
        } else {
            $_SESSION['message'] = "Error removing tournament: " . $stmt->error;
        }
        $stmt->close();
    }

    header('Location: update_status.php'); // Redirect to refresh the page after update
    exit();
}

// Retrieve all pending tournaments
$stmt = $conn->prepare("SELECT tournament_id, name, sport, payment_reference, status FROM tournaments WHERE status = 'pending' AND deleted = 0");
if (!$stmt) {
    die('Prepare failed: ' . $conn->error);
}
$stmt->execute();
$result = $stmt->get_result();
$tournaments = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Tournaments</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
        }

        .container {
            flex: 1;
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

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background-color: #f4f4f4;
        }

        button {
            padding: 5px 10px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }

        form {
            display: inline;
        }

        .message {
            margin-bottom: 20px;
            padding: 10px;
            border: 1px solid #ddd;
            background-color: #e9ecef;
            color: #333;
            border-radius: 3px;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
            margin-top: 10px;
        }

        .btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <?php include 'admin_sidebar.php'; ?>
    <div class="container">
        <h2>Update Status</h2>
        <?php if (isset($_SESSION['message'])): ?>
            <div class="message">
                <?php
                echo $_SESSION['message'];
                unset($_SESSION['message']); // Clear message after displaying
                ?>
            </div>
        <?php endif; ?>
        <?php if (empty($tournaments)): ?>
            <p>No tournaments in pending condition.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Tournament Name</th>
                        <th>Sport</th>
                        <th>Payment Reference</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tournaments as $tournament): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($tournament['name']); ?></td>
                            <td><?php echo htmlspecialchars($tournament['sport']); ?></td>
                            <td><?php echo htmlspecialchars($tournament['payment_reference']); ?></td>
                            <td><?php echo htmlspecialchars($tournament['status']); ?></td>
                            <td>
                                <form action="update_status.php" method="post">
                                    <input type="hidden" name="tournament_id" value="<?php echo $tournament['tournament_id']; ?>">
                                    <?php if ($tournament['status'] === 'pending'): ?>
                                        <button type="submit" name="action" value="approve">Approve</button>
                                    <?php endif; ?>
                                    <?php if ($tournament['status'] === 'approved'): ?>
                                        <button type="submit" name="action" value="pending">Pending</button>
                                    <?php endif; ?>
                                    <button type="submit" name="action" value="remove">Remove</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        <a href="manage_tournaments.php" class="btn">Go Back</a>
    </div>
</body>
</html>

<?php $conn->close(); ?>
