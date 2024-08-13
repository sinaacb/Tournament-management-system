<?php
session_start();
require_once 'db.php';

$success_message = '';
$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $team_name = $_POST['team_name'];
    $tournament_id = $_POST['tournament_id'];
    $leader_name = $_POST['leader_name'];
    $leader_contact = $_POST['leader_contact'];
    $members = $_POST['members'];
    $payment_reference = $_POST['payment_reference'];
    $registered_by = $_SESSION['user_id']; // Get user ID from session

    // Check team spots before registering
    $stmt = $conn->prepare("SELECT team_spot_left FROM tournaments WHERE tournament_id = ?");
    $stmt->bind_param("i", $tournament_id);
    $stmt->execute();
    $stmt->bind_result($team_spot_left);
    $stmt->fetch();
    $stmt->close();

    if ($team_spot_left > 0) {
        $conn->begin_transaction();

        try {
            // Insert team details
            $stmt = $conn->prepare("INSERT INTO teams (team_name, tournament_id, leader_name, leader_contact, members, payment_reference, registered_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sisssss", $team_name, $tournament_id, $leader_name, $leader_contact, $members, $payment_reference, $registered_by);
            if (!$stmt->execute()) {
                throw new Exception("Error inserting team: " . $stmt->error);
            }
            $stmt->close();

            // Update team_spot_left
            $stmt = $conn->prepare("UPDATE tournaments SET team_spot_left = team_spot_left - 1 WHERE tournament_id = ?");
            $stmt->bind_param("i", $tournament_id);
            if (!$stmt->execute()) {
                throw new Exception("Error updating team spots: " . $stmt->error);
            }
            $stmt->close();

            $conn->commit();
            $success_message = "Team registered successfully.";
        } catch (Exception $e) {
            $conn->rollback();
            $error_message = "Error: " . $e->getMessage();
        }
    } else {
        $error_message = "No team spot left for the specified tournament.";
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register Team</title>
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
            margin-left: 220px;
            padding: 20px;
            width: 100%;
        }

        h2 {
            text-align: center;
            color: #333;
            margin-top: 20px;
        }

        form {
            max-width: 500px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input[type="text"],
        input[type="number"],
        textarea,
        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        button {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }

        .qr-code {
            text-align: center;
            margin-bottom: 20px;
        }

        .qr-code img {
            width: 200px;
            height: 200px;
        }

        .registration-fee {
            text-align: center;
            margin-bottom: 20px;
            font-size: 18px;
            color: #333;
        }

        .tournament-description {
            text-align: center;
            margin-bottom: 20px;
            padding: 10px;
            background-color: #e9e9e9;
            border-radius: 4px;
            font-size: 16px;
            color: #333;
        }

        .message {
            text-align: center;
            margin-bottom: 20px;
            font-size: 18px;
            color: #333;
        }

        .go-back {
            text-align: center;
            margin-top: 20px;
        }
    </style>
    <script>
        function updateRegistrationFeeAndDescription() {
            const tournamentSelect = document.getElementById('tournament_id');
            const feeDisplay = document.getElementById('registration_fee_display');
            const descriptionBox = document.getElementById('tournament_description');
            const tournamentId = tournamentSelect.value;

            if (tournamentId) {
                fetch(`get_tournament_details.php?tournament_id=${tournamentId}`)
                    .then(response => response.json())
                    .then(data => {
                        feeDisplay.innerText = `Registration Fee: ₹${data.registration_fee}`;
                        descriptionBox.innerHTML = `
                            <p><strong>Name:</strong> ${data.name}</p>
                            <p><strong>Sport:</strong> ${data.sport}</p>
                            <p><strong>Gender:</strong> ${data.gender}</p>
                            <p><strong>Category:</strong> ${data.category}</p>
                            <p><strong>Date:</strong> ${data.date}</p>
                            <p><strong>Type:</strong> ${data.type}</p>
                            <p><strong>Location:</strong> ${data.location}</p>
                            <p><strong>Organizer Name:</strong> ${data.organizer_name}</p>
                            <p><strong>Contact No:</strong> ${data.contact_no}</p>
                            <p><strong>Registration Deadline:</strong> ${data.registration_deadline}</p>
                        `;
                    })
                    .catch(error => console.error('Error fetching tournament details:', error));
            } else {
                feeDisplay.innerText = '';
                descriptionBox.innerText = '';
            }
        }
    </script>
</head>
<body>
    <?php include 'client_sidebar.php'; ?>
    <div class="container">
        <h2>Register Team</h2>

        <?php if ($success_message): ?>
            <div class="message">
                <p><?php echo $success_message; ?></p>
                <form action="register_team.php" method="get">
                    <button type="submit">Register More Teams</button>
                </form>
            </div>
        <?php elseif ($error_message): ?>
            <div class="message">
                <p><?php echo $error_message; ?></p>
            </div>
        <?php else: ?>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <label for="team_name">Team Name:</label>
                <input type="text" id="team_name" name="team_name" required>

                <label for="tournament_id">Tournament:</label>
                <select id="tournament_id" name="tournament_id" onchange="updateRegistrationFeeAndDescription()" required>
                    <option value="">Select a tournament</option>
                    <?php
                    // Fetch tournaments from the database
                    $query = "SELECT tournament_id, name FROM tournaments WHERE status = 'approved' AND deleted = false";
                    $result = mysqli_query($conn, $query);
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo '<option value="' . $row['tournament_id'] . '">' . $row['name'] . '</option>';
                    }
                    ?>
                </select>

                <div class="registration-fee" id="registration_fee_display"></div>
                <div class="tournament-description" id="tournament_description"></div>

                <label for="leader_name">Captain Name:</label>
                <input type="text" id="leader_name" name="leader_name" required>

                <label for="leader_contact">Captain Contact:</label>
                <input type="text" id="leader_contact" name="leader_contact" required>

                <label for="members">Team Members (comma-separated):</label>
                <textarea id="members" name="members" required></textarea>

                <div class="qr-code">
                    <p>Scan the QR code to pay the registration fee:</p>
                    <img src="uploads/qr4.png" alt="QR Code">
                </div>

                <label for="payment_reference">Payment Reference:</label>
                <input type="text" id="payment_reference" name="payment_reference" required>

                <button type="submit">Register Team</button>
            </form>
        <?php endif; ?>

        <div class="go-back">
            <form action="client.php" method="get">
                <button type="submit">Go Back</button>
                </form>
        </div>
    </div>
</body>
</html>
           
