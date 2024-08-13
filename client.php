<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'client') {
    header('Location: index.php');
    exit();
}

require_once 'db.php';


// Fetch user details
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT firstname, lastname FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($firstname, $lastname);

if ($stmt->fetch()) {
    $welcome_message = "Welcome, " . htmlspecialchars($firstname) . " " . htmlspecialchars($lastname) . "!";
} else {
    $welcome_message = "Welcome, Client!";
}

$stmt->close();

// Fetch file paths from the tournaments table
$file_query = "SELECT details FROM tournaments WHERE status = 'approved' AND deleted = false";
$file_result = $conn->query($file_query);

$files = [];
if ($file_result) {
    while ($row = $file_result->fetch_assoc()) {
        if (!empty($row['details'])) {
            $files[] = $row['details'];
        }
    }
}

// Shuffle the files array to randomize the order
shuffle($files);

// Read file contents
$file_contents = [];
foreach ($files as $file_path) {
    $file_path = trim($file_path); // Clean the file path
    if (file_exists($file_path)) {
        $file_contents[] = [
            'path' => $file_path,
            'content' => file_get_contents($file_path)
        ];
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Client Panel</title>
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

        ul {
            list-style-type: none;
            padding: 0;
        }

        li {
            margin-bottom: 10px;
        }

        a {
            display: block;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
        }

        a:hover {
            background-color: #0056b3;
        }

        .slideshow-container {
            max-width: 800px;
            margin: 20px auto;
            position: relative;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .slide {
            display: none;
            padding: 20px;
        }

        .slide img {
            max-width: 100%;
            height: auto;
        }

        .slide pre {
            white-space: pre-wrap; /* Preserve whitespace formatting */
            background: #f4f4f4;
            padding: 10px;
            border-radius: 5px;
            overflow: auto;
        }

        .prev, .next {
            cursor: pointer;
            position: absolute;
            top: 50%;
            width: auto;
            padding: 16px;
            margin-top: -22px;
            color: #fff;
            font-weight: bold;
            font-size: 18px;
            border-radius: 0 3px 3px 0;
            user-select: none;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .next {
            right: 0;
            border-radius: 3px 0 0 3px;
        }

        .prev:hover, .next:hover {
            background-color: rgba(0, 0, 0, 0.8);
        }
    </style>
</head>
<body>
    <div class="container">
        <h2><?php echo $welcome_message; ?></h2>
        <ul>
            <li><a href="view_tournaments.php">View Tournaments</a></li>
            <li><a href="register_tournament.php">Register Tournament</a></li>
            <li><a href="register_team.php">Register Team</a></li>
            <li><a href="view_registered_tournaments.php">My Tournaments</a></li>
            <li><a href="view_registered_teams.php">My Teams</a></li>
            <li><a href="logout.php">Log Out</a></li>
        </ul>
    </div>

    <div class="slideshow-container">
        <?php if (count($file_contents) > 0): ?>
            <?php foreach ($file_contents as $index => $file): ?>
                <div class="slide" id="slide-<?php echo $index; ?>">
                    <?php
                    // Display file content based on its type
                    $file_extension = pathinfo($file['path'], PATHINFO_EXTENSION);
                    if ($file_extension === 'jpg' || $file_extension === 'jpeg' || $file_extension === 'png' || $file_extension === 'gif'): ?>
                        <img src="<?php echo htmlspecialchars($file['path']); ?>" alt="Slide <?php echo $index + 1; ?>">
                    <?php else: ?>
                        <pre><?php echo htmlspecialchars($file['content']); ?></pre>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No files available.</p>
        <?php endif; ?>

        <a class="prev" onclick="plusSlides(-1)">&#10094;</a>
        <a class="next" onclick="plusSlides(1)">&#10095;</a>
    </div>

    <script>
        let slideIndex = 0;
        const slideInterval = 5000; // Slide change interval in milliseconds (5 seconds)

        function showSlides() {
            let i;
            const slides = document.getElementsByClassName("slide");
            for (i = 0; i < slides.length; i++) {
                slides[i].style.display = "none";
            }
            slideIndex++;
            if (slideIndex > slides.length) { slideIndex = 1; }
            slides[slideIndex - 1].style.display = "block";
            setTimeout(showSlides, slideInterval); // Change slide every 5 seconds
        }

        function plusSlides(n) {
            showSlides(slideIndex += n);
        }

        showSlides(); // Initialize slideshow
    </script>
</body>
</html>
