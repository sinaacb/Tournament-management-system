<?php
session_start();
require_once 'db.php';

// Check if the user is logged in and is an admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tournament_id = $_POST['tournament_id'];
    $stmt = $conn->prepare("UPDATE tournaments SET deleted = 1 WHERE tournament_id = ?");
    $stmt->bind_param("i", $tournament_id);

    if ($stmt->execute()) {
        $success_message = "Tournament removed successfully.";
    } else {
        $error_message = "Error removing tournament: " . $stmt->error;
    }

    $stmt->close();
}

$sport_filter = $_GET['sport'] ?? '';
$gender_filter = $_GET['gender'] ?? '';
$category_filter = $_GET['category'] ?? '';
$type_filter = $_GET['type'] ?? '';

$query = "SELECT * FROM tournaments WHERE status = 'approved' AND deleted = 0";

$filters = [];
if ($sport_filter) {
    $filters[] = "sport = '$sport_filter'";
}
if ($gender_filter) {
    $filters[] = "gender = '$gender_filter'";
}
if ($category_filter) {
    $filters[] = "category = '$category_filter'";
}
if ($type_filter) {
    $filters[] = "type = '$type_filter'";
}

if ($filters) {
    $query .= " AND " . implode(" AND ", $filters);
}

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Delete Tournaments</title>
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
            max-width: 1200px;
            margin: 20px auto;
            margin-left: 240px;
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
            margin-top: 20px;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .filter-container {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .filter-container select {
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }

        .action-button {
            padding: 5px 10px;
            background-color: #ff4d4d;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .action-button:hover {
            background-color: #cc0000;
        }

        .message {
            text-align: center;
            margin-bottom: 20px;
            font-size: 18px;
            color: #333;
        }

        .back-button {
            display: block;
            width: 100%;
            margin-top: 20px;
            text-align: center;
        }

        .back-button button {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .back-button button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <?php include 'admin_sidebar.php'; ?>
    <div class="container">
        <h2>Delete Tournaments</h2>

        <?php if (isset($success_message)): ?>
            <div class="message">
                <p><?php echo $success_message; ?></p>
            </div>
        <?php elseif (isset($error_message)): ?>
            <div class="message">
                <p><?php echo $error_message; ?></p>
            </div>
        <?php endif; ?>

        <div class="filter-container">
            <form method="GET" action="">
                <select name="sport" onchange="this.form.submit()">
                    <option value="">All Sports</option>
                    <?php
                    $sport_query = "SELECT DISTINCT sport FROM tournaments WHERE status = 'approved' AND deleted = 0";
                    $sport_result = mysqli_query($conn, $sport_query);
                    while ($row = mysqli_fetch_assoc($sport_result)) {
                        $selected = ($sport_filter == $row['sport']) ? 'selected' : '';
                        echo "<option value='" . $row['sport'] . "' $selected>" . $row['sport'] . "</option>";
                    }
                    ?>
                </select>

                <select name="gender" onchange="this.form.submit()">
                    <option value="">All Genders</option>
                    <?php
                    $gender_query = "SELECT DISTINCT gender FROM tournaments WHERE status = 'approved' AND deleted = 0";
                    $gender_result = mysqli_query($conn, $gender_query);
                    while ($row = mysqli_fetch_assoc($gender_result)) {
                        $selected = ($gender_filter == $row['gender']) ? 'selected' : '';
                        echo "<option value='" . $row['gender'] . "' $selected>" . $row['gender'] . "</option>";
                    }
                    ?>
                </select>

                <select name="category" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    <?php
                    $category_query = "SELECT DISTINCT category FROM tournaments WHERE status = 'approved' AND deleted = 0";
                    $category_result = mysqli_query($conn, $category_query);
                    while ($row = mysqli_fetch_assoc($category_result)) {
                        $selected = ($category_filter == $row['category']) ? 'selected' : '';
                        echo "<option value='" . $row['category'] . "' $selected>" . $row['category'] . "</option>";
                    }
                    ?>
                </select>

                <select name="type" onchange="this.form.submit()">
                    <option value="">All Types</option>
                    <?php
                    $type_query = "SELECT DISTINCT type FROM tournaments WHERE status = 'approved' AND deleted = 0";
                    $type_result = mysqli_query($conn, $type_query);
                    while ($row = mysqli_fetch_assoc($type_result)) {
                        $selected = ($type_filter == $row['type']) ? 'selected' : '';
                        echo "<option value='" . $row['type'] . "' $selected>" . $row['type'] . "</option>";
                    }
                    ?>
                </select>
            </form>
        </div>

        <table>
            <tr>
                <th>Name</th>
                <th>Sport</th>
                <th>Gender</th>
                <th>Category</th>
                <th>Type</th>
                <th>Action</th>
            </tr>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['sport']); ?></td>
                    <td><?php echo htmlspecialchars($row['gender']); ?></td>
                    <td><?php echo htmlspecialchars($row['category']); ?></td>
                    <td><?php echo htmlspecialchars($row['type']); ?></td>
                    <td>
                        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" style="display:inline;">
                            <input type="hidden" name="tournament_id" value="<?php echo $row['tournament_id']; ?>">
                            <button type="submit" class="action-button">Remove Tournament</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>

        <div class="back-button">
            <button onclick="window.location.href='manage_tournaments.php'">Go Back</button>
        </div>
    </div>
</body>
</html>

<?php $conn->close(); ?>
