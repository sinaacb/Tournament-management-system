<?php
session_start();

// Ensure the user is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit();
}

require_once 'db.php';

// Get tournament ID from query parameter
$tournament_id = isset($_GET['tournament_id']) ? $_GET['tournament_id'] : null;

if (!$tournament_id) {
    echo "Invalid tournament ID.";
    exit();
}

// Fetch tournament details
$query = "SELECT name, sport, registration_fee, confirmed_teams, deleted FROM tournaments WHERE tournament_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $tournament_id);
$stmt->execute();
$result = $stmt->get_result();
$tournament = $result->fetch_assoc();

if (!$tournament) {
    echo "Tournament not found.";
    exit();
}

// Fetch tournament payment details
$query = "SELECT teams_registered, teams_removed, pending_payment, received_payment, settled_teams FROM tournament_payments WHERE tournament_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $tournament_id);
$stmt->execute();
$result = $stmt->get_result();
$payment_details = $result->fetch_assoc();

if (!$payment_details) {
    echo "Payment details not found.";
    exit();
}

// Calculate revenues
$registration_fee = $tournament['registration_fee'];
$teams_registered = $payment_details['teams_registered'];
$teams_removed = $payment_details['teams_removed'];

$revenue_generated = $teams_registered * $registration_fee;
$revenue_lost = $teams_removed * $registration_fee;
$net_revenue = $revenue_generated - $revenue_lost;

// Handle payment settlement
$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pending_payment = $payment_details['pending_payment'];
    $received_payment = $payment_details['received_payment'];
    $deleted = $tournament['deleted'];
    $settled_teams = $payment_details['settled_teams'];
    $confirmed_teams = $tournament['confirmed_teams'];

    if ($pending_payment == 0) {
        $message = "No amount left to settle.";
    } else {
        if ($deleted == false) {
            $teams_left_to_settle = $teams_registered - $teams_removed;
            if ($teams_left_to_settle > $confirmed_teams) {
                if ($settled_teams < $confirmed_teams) {
                    $teams_to_settle = $confirmed_teams - $settled_teams;
                    $amount_to_transfer = $teams_to_settle * $registration_fee;
                    $new_pending_payment = $pending_payment - $amount_to_transfer;
                    $new_received_payment = $received_payment + $amount_to_transfer;
                    $new_settled_teams = $settled_teams + $teams_to_settle;

                    // Update payment details in the database
                    $query = "UPDATE tournament_payments SET pending_payment = ?, received_payment = ?, settled_teams = ? WHERE tournament_id = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("ddii", $new_pending_payment, $new_received_payment, $new_settled_teams, $tournament_id);
                    $stmt->execute();

                    // Refresh payment details
                    header("Location: payment_details.php?tournament_id=$tournament_id");
                    exit();
                } else {
                    $message = "Cannot settle payment: There are still teams left to confirm.";
                }
            } else {
                $amount_to_transfer = $pending_payment;
                $new_pending_payment = 0;
                $new_received_payment = $received_payment + $amount_to_transfer;

                // Update payment details in the database
                $query = "UPDATE tournament_payments SET pending_payment = ?, received_payment = ? WHERE tournament_id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("ddi", $new_pending_payment, $new_received_payment, $tournament_id);
                $stmt->execute();

                // Refresh payment details
                header("Location: payment_details.php?tournament_id=$tournament_id");
                exit();
            }
        } else {
            if ($tournament['deleted'] == true) {
                $amount_to_transfer = $pending_payment;
                $new_pending_payment = 0;
                $new_received_payment = $received_payment + $amount_to_transfer;

                // Update payment details in the database
                $query = "UPDATE tournament_payments SET pending_payment = ?, received_payment = ? WHERE tournament_id = ?";
                $stmt->bind_param("ddi", $new_pending_payment, $new_received_payment, $tournament_id);
                $stmt->execute();

                // Refresh payment details
                header("Location: payment_details.php?tournament_id=$tournament_id");
                exit();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin - Payment Details</title>
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
            width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-left: 400px; /* Adjust for sidebar */
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .action-link {
            color: #007bff;
            text-decoration: none;
        }

        .action-link:hover {
            text-decoration: underline;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            color: #fff;
            background-color: #4CAF50;
            text-decoration: none;
            border-radius: 4px;
            text-align: center;
        }

        .btn:hover {
            background-color: #45a049;
        }

        .message {
            color: red;
            text-align: center;
            margin-bottom: 20px;
        }

        .go-back {
            display: inline-block;
            padding: 10px 20px;
            color: #fff;
            background-color: #007bff;
            text-decoration: none;
            border-radius: 4px;
            text-align: center;
            margin-top: 20px;
        }

        .go-back:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <?php include 'admin_sidebar.php'; ?>
    <div class="container">
        <h2>Payment Details of <?php echo htmlspecialchars($tournament['name']); ?></h2>
        <?php if ($message): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <table>
            <tr>
                <th>Tournament ID</th>
                <td><?php echo htmlspecialchars($tournament_id); ?></td>
            </tr>
            <tr>
                <th>Name</th>
                <td><?php echo htmlspecialchars($tournament['name']); ?></td>
            </tr>
            <tr>
                <th>Sport</th>
                <td><?php echo htmlspecialchars($tournament['sport']); ?></td>
            </tr>
            <tr>
                <th>Registration Fee</th>
                <td><?php echo htmlspecialchars($registration_fee); ?></td>
            </tr>
            <tr>
                <th>Teams Registered</th>
                <td><?php echo htmlspecialchars($teams_registered); ?></td>
            </tr>
            <tr>
                <th>Total Revenue Generated</th>
                <td><?php echo htmlspecialchars($revenue_generated); ?></td>
            </tr>
            <tr>
                <th>Teams Removed</th>
                <td><?php echo htmlspecialchars($teams_removed); ?></td>
            </tr>
            <tr>
                <th>Revenue Lost</th>
                <td><?php echo htmlspecialchars($revenue_lost); ?></td>
            </tr>
            <tr>
                <th>Net Revenue</th>
                <td><?php echo htmlspecialchars($net_revenue); ?></td>
            </tr>
            <tr>
                <th>Teams Confirmed</th>
                <td><?php echo htmlspecialchars($tournament['confirmed_teams']); ?></td>
            </tr>
            <tr>
                <th>Teams Settled</th>
                <td><?php echo htmlspecialchars($payment_details['settled_teams']); ?></td>
            </tr>
            <tr>
                <th>Pending Payment</th>
                <td><?php echo htmlspecialchars($payment_details['pending_payment']); ?></td>
            </tr>
            <tr>
                <th>Received Payment</th>
                <td><?php echo htmlspecialchars($payment_details['received_payment']); ?></td>
            </tr>
        </table>
        <form method="post">
            <input type="submit" class="btn" value="Settle Payment">
        </form>
        <a href="settle_payments.php" class="go-back">Go Back</a>
    </div>
</body>
</html>
