<?php
session_start();
require_once 'db.php';

$message = ''; // Variable to store messages

// Ensure the user is logged in and has the correct role
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'client') {
    header('Location: index.php');
    exit();
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_FILES['fixture']) && $_FILES['fixture']['error'] == UPLOAD_ERR_OK) {
        // Ensure a tournament ID is provided
        if (isset($_POST['tournament_id'])) {
            $tournament_id = intval($_POST['tournament_id']); // Ensure the ID is an integer

            // Directory where the PDF files will be uploaded
            $uploadDir = '/Users/sinaa/Sites/tournament/fixtures/';
            $uploadFile = $uploadDir . basename($_FILES['fixture']['name']);

            // Check if the file is a PDF
            $fileType = strtolower(pathinfo($uploadFile, PATHINFO_EXTENSION));
            if ($fileType != 'pdf') {
                $message = "Only PDF files are allowed.";
            } else {
                // Move the uploaded file to the desired directory
                if (move_uploaded_file($_FILES['fixture']['tmp_name'], $uploadFile)) {
                    // Update the database with the file path
                    $fixturePath = 'fixtures/' . basename($_FILES['fixture']['name']);
                    $query = "UPDATE tournaments SET fixtures = ? WHERE tournament_id = ?";

                    if ($stmt = $conn->prepare($query)) {
                        $stmt->bind_param("si", $fixturePath, $tournament_id);
                        if ($stmt->execute()) {
                            $message = "Fixture uploaded successfully.";
                        } else {
                            $message = "Error updating database: " . $stmt->error;
                        }
                        $stmt->close();
                    } else {
                        $message = "Error preparing query: " . $conn->error;
                    }
                } else {
                    $message = "Error uploading file.";
                }
            }
        } else {
            $message = "No tournament ID provided.";
        }
    } else {
        $message = "No file uploaded or there was an upload error.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Upload Fixture</title>
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

        form {
            max-width: 600px;
            margin: 0 auto;
        }

        input[type="file"] {
            display: block;
            margin-bottom: 10px;
        }

        input[type="submit"] {
            background-color: #007bff;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
        }

        .message {
            color: red;
            text-align: center;
            margin-top: 10px;
        }
        .go-back-btn {
            display: block;
            margin: 20px auto;
            padding: 10px 20px;
            color: #fff;
            background-color: #007bff;
            text-decoration: none;
            border-radius: 4px;
            text-align: center;
            width: 150px;
        }

        .go-back-btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Upload Fixture</h2>
        <form action="upload_fixtures.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="tournament_id" value="<?php echo htmlspecialchars($_GET['tournament_id'] ?? ''); ?>">
            <input type="file" name="fixture" required>
            <input type="submit" value="Upload">
        </form>
        <br>
        <div class="go-back">
            <form action="view_registered_tournaments.php" method="get">
                <button type="submit" class="go-back-btn">Go Back</button>
            </form>
        </div>

    </div>
    <div class="message">
        <?php
        if (!empty($message)) {
            echo htmlspecialchars($message);
        }
        ?>
    </div>
</body>
</html>
