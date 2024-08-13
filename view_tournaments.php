<?php
session_start();
require_once 'db.php';

$tournaments = [];
$no_tournaments_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['search_by_id'])) {
        $tournament_id = intval($_POST['tournament_id']); // Ensure the ID is an integer
        $query = "SELECT * FROM tournaments WHERE tournament_id = $tournament_id AND deleted = FALSE AND status = 'approved'";
        $result = mysqli_query($conn, $query);
        if ($result && mysqli_num_rows($result) > 0) {
            $tournaments = mysqli_fetch_all($result, MYSQLI_ASSOC);
        } else {
            $no_tournaments_message = "No tournaments found.";
        }
    } else if (isset($_POST['search_by_criteria'])) {
        $sport = mysqli_real_escape_string($conn, $_POST['sport']);
        $category = mysqli_real_escape_string($conn, $_POST['category']);
        $type = mysqli_real_escape_string($conn, $_POST['type']);
        $gender = mysqli_real_escape_string($conn, $_POST['gender']);

        $query = "SELECT tournament_id, name, location FROM tournaments WHERE deleted = FALSE AND status = 'approved' AND sport = '$sport' AND category = '$category' AND type = '$type' AND gender = '$gender'";
        $result = mysqli_query($conn, $query);
        if ($result && mysqli_num_rows($result) > 0) {
            $tournaments = mysqli_fetch_all($result, MYSQLI_ASSOC);
        } else {
            $no_tournaments_message = "No tournaments found.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>View Tournaments</title>
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
            margin-left: 400px; /* Adjusted to match sidebar width */
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            max-width: 800px;
            width: 800px;
        }

        h2 {
            color: #333;
            margin-top: 0;
            text-align: center;
        }

        h3 {
            color: #333;
            margin-top: 0;
        }

        form {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-top: 10px;
        }

        select, input[type="text"] {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
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
            background-color: #575757;
        }

        ul {
            list-style-type: none;
            padding: 0;
            margin-top: 20px;
        }

        li {
            margin-bottom: 10px;
            padding: 10px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        li:hover {
            background-color: #f9f9f9;
        }

        a {
            display: inline-block;
            padding: 5px 10px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }

        a:hover {
            background-color: #0056b3;
        }

        .message {
            color: #ff0000;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <?php include 'client_sidebar.php'; ?>
    <div class="container">
        <h2>View Tournaments</h2>
        
        <h3>Search by ID</h3>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <label>Tournament ID:</label>
            <input type="text" name="tournament_id" required>
            <button type="submit" name="search_by_id">Search</button>
        </form>

        <h3>Search by Criteria</h3>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
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
            <label>Type:</label>
            <select name="type" required>
                <option value="5s">5s</option>
                <option value="7s">7s</option>
                <option value="11s">11s</option>
                <option value="singles">Singles</option>
                <option value="doubles">Doubles</option>
                <option value="nil">NIL</option>
            </select>
            <label>Gender:</label>
            <select name="gender" required>
                <option value="male">Male</option>
                <option value="female">Female</option>
            </select>
            <button type="submit" name="search_by_criteria">Search</button>
        </form>

        <?php if (!empty($tournaments)): ?>
        <h3>Results:</h3>
        <ul>
            <?php foreach ($tournaments as $tournament): ?>
                <li>
                    <?php echo htmlspecialchars($tournament['name']); ?> - <?php echo htmlspecialchars($tournament['location']); ?>
                    <form action="view_details.php" method="get" style="display: inline;">
                        <input type="hidden" name="tournament_id" value="<?php echo htmlspecialchars($tournament['tournament_id']); ?>">
                        <button type="submit">View Details</button>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>

        <?php if (!empty($no_tournaments_message)): ?>
            <p class="message"><?php echo htmlspecialchars($no_tournaments_message); ?></p>
        <?php endif; ?>

        <!-- Go Back Button -->
        <form action="client.php" method="get" style="margin-top: 20px;">
            <button type="submit">Go Back</button>
        </form>
    </div>
</body>
</html>
