<?php
session_start();
require_once 'db.php';

$success_message = '';
$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $sport = $_POST['sport'];
    $gender = $_POST['gender'];
    $category = $_POST['category'];
    $date = $_POST['date'];
    $type = $_POST['type'];
    $location = $_POST['location'];
    $team_spot_left = $_POST['team_spot_left'];
    $organizer_name = $_POST['organizer_name'];
    $contact_no = $_POST['contact_no'];
    $registration_deadline = $_POST['registration_deadline'];
    $registration_fee = $_POST['registration_fee'];
    $payment_reference = $_POST['payment_reference'];
    $location_link = $_POST['location_link'];
    $created_by = $_SESSION['user_id']; // Get user ID from session

    $file_tmp = $_FILES['details']['tmp_name'];
    $file_name = basename($_FILES['details']['name']);
    $target_dir = "uploads/";
    $target_file = $target_dir . $file_name;

    if (move_uploaded_file($file_tmp, $target_file)) {
        $details = $target_file;
        $stmt = $conn->prepare("INSERT INTO tournaments (name, sport, gender, category, date, type, details, location, team_spot_left, organizer_name, contact_no, registration_deadline, registration_fee, payment_reference, location_link, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssssssssssss", $name, $sport, $gender, $category, $date, $type, $details, $location, $team_spot_left, $organizer_name, $contact_no, $registration_deadline, $registration_fee, $payment_reference, $location_link, $created_by);

        if ($stmt->execute()) {
            $success_message = "Tournament registered successfully.";
        } else {
            $error_message = "Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        $error_message = "Error uploading file.";
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register Tournament</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
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
            width: calc(100% - 240px);
        }

        h2 {
            color: #333;
            text-align: center;
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
        }

        input[type="text"],
        input[type="date"],
        input[type="number"],
        input[type="file"],
        select {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
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

        .message {
            text-align: center;
            margin: 20px;
            font-size: 18px;
            color: #333;
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
        <h2>Register Tournament</h2>

        <?php if ($success_message): ?>
            <div class="message">
                <p><?php echo $success_message; ?></p>
            </div>
        <?php elseif ($error_message): ?>
            <div class="message">
                <p><?php echo $error_message; ?></p>
            </div>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
            <label>Name:</label>
            <input type="text" name="name" required>
            
            <label>Sport:</label>
            <select name="sport" required>
                <option value="football">Football</option>
                <option value="cricket">Cricket</option>
                <option value="badminton">Badminton</option>
                <option value="kabaddi">Kabaddi</option>
                <option value="volleyball">Volleyball</option>
                <option value="handball">Handball</option>
                <option value="kho-kho">Kho-Kho</option>
            </select>
            
            <label>Gender:</label>
            <select name="gender" required>
                <option value="male">Male</option>
                <option value="female">Female</option>
            </select>
            
            <label>Category:</label>
            <select name="category" required>
                <option value="U10">U10</option>
                <option value="U14">U14</option>
                <option value="U16">U16</option>
                <option value="U18">U18</option>
                <option value="U20">U20</option>
                <option value="U24">U24</option>
                <option value="open">Open</option>
                <option value="U40kg">U40kg</option>
                <option value="U50kg">U50kg</option>
                <option value="U60kg">U60kg</option>
                <option value="U70kg">U70kg</option>
            </select>
            
            <label>Date:</label>
            <input type="date" name="date" required>
            
            <label>Type:</label>
            <select name="type" required>
                <option value="5s">5s</option>
                <option value="7s">7s</option>
                <option value="11s">11s</option>
                <option value="singles">Singles</option>
                <option value="doubles">Doubles</option>
                <option value="nil">NIL</option>
            </select>
            
            <label>Details :</label>
            <input type="file" name="details" accept=".pdf,.jpg" required>
            
            <label>Location:</label>
            <input type="text" name="location" required>
            
            <label>Team Spots Left:</label>
            <input type="number" name="team_spot_left" required>
            
            <label>Organizer Name:</label>
            <input type="text" name="organizer_name" required>
            
            <label>Contact No:</label>
            <input type="text" name="contact_no" required>
            
            <label>Registration Deadline:</label>
            <input type="date" name="registration_deadline" required>
            
            <label>Registration Fee:</label>
            <input type="number" name="registration_fee" step="0.01" required>
            
            <div class="qr-code">
                <p>Scan the QR code to pay the registration fee (₹250):</p>
                <img src="uploads/qr4.png" alt="QR Code">
            </div>
            
            <label>Payment Reference:</label>
            <input type="text" name="payment_reference" required>
            
            <label>Location Link:</label>
            <input type="text" name="location_link">
            
            <button type="submit">Register</button>
        </form>

        <div class="go-back">
            <form action="client.php" method="get">
                <button type="submit">Go Back</button>
            </form>
        </div>
    </div>
</body>
</html>
