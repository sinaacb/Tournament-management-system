<?php
session_start();
require_once 'db.php';

// Check if the user is logged in and is an admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$sport_filter = $_GET['sport'] ?? '';
$gender_filter = $_GET['gender'] ?? '';
$type_filter = $_GET['type'] ?? '';
$status_filter = $_GET['status'] ?? '';

$query = "SELECT * FROM tournaments WHERE deleted = 0";

$filters = [];
if ($sport_filter) {
    $filters[] = "sport = '$sport_filter'";
}
if ($gender_filter) {
    $filters[] = "gender = '$gender_filter'";
}
if ($type_filter) {
    $filters[] = "type = '$type_filter'";
}
if ($status_filter) {
    $filters[] = "status = '$status_filter'";
}

if ($filters) {
    $query .= " AND " . implode(" AND ", $filters);
}

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Tournaments</title>
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
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .action-button:hover {
            background-color: #0056b3;
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
        <h2>View Tournaments</h2>

        <div class="filter-container">
            <form method="GET" action="">
                <select name="sport" onchange="this.form.submit()">
                    <option value="">All Sports</option>
                    <?php
                    $sport_query = "SELECT DISTINCT sport FROM tournaments WHERE deleted = 0";
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
                    $gender_query = "SELECT DISTINCT gender FROM tournaments WHERE deleted = 0";
                    $gender_result = mysqli_query($conn, $gender_query);
                    while ($row = mysqli_fetch_assoc($gender_result)) {
                        $selected = ($gender_filter == $row['gender']) ? 'selected' : '';
                        echo "<option value='" . $row['gender'] . "' $selected>" . $row['gender'] . "</option>";
                    }
                    ?>
                </select>

                <select name="type" onchange="this.form.submit()">
                    <option value="">All Types</option>
                    <?php
                    $type_query = "SELECT DISTINCT type FROM tournaments WHERE deleted = 0";
                    $type_result = mysqli_query($conn, $type_query);
                    while ($row = mysqli_fetch_assoc($type_result)) {
                        $selected = ($type_filter == $row['type']) ? 'selected' : '';
                        echo "<option value='" . $row['type'] . "' $selected>" . $row['type'] . "</option>";
                    }
                    ?>
                </select>

                <select name="status" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <?php
                    $status_query = "SELECT DISTINCT status FROM tournaments WHERE deleted = 0";
                    $status_result = mysqli_query($conn, $status_query);
                    while ($row = mysqli_fetch_assoc($status_result)) {
                        $selected = ($status_filter == $row['status']) ? 'selected' : '';
                        echo "<option value='" . $row['status'] . "' $selected>" . $row['status'] . "</option>";
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
                <th>Type</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['sport']); ?></td>
                    <td><?php echo htmlspecialchars($row['gender']); ?></td>
                    <td><?php echo htmlspecialchars($row['type']); ?></td>
                    <td><?php echo htmlspecialchars($row['status']); ?></td>
                    <td>
                        <form method="GET" action="view_tournament_details.php" style="display:inline;">
                            <input type="hidden" name="tournament_id" value="<?php echo $row['tournament_id']; ?>">
                            <button type="submit" class="action-button">View Details</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
        <a href="admin.php" class="go-back">Go Back</a>
    </div>
</body>
</html>

<?php $conn->close(); ?>
