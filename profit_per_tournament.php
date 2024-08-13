<?php
session_start();

// Ensure the user is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit();
}

require_once 'db.php';

// Initialize variables for filtering
$name_filter = $_GET['name'] ?? '';
$sport_filter = $_GET['sport'] ?? '';

// Fetch tournament details along with their profit based on the filter
$query = "
    SELECT t.tournament_id, t.name, t.sport, p.profit_per_tournament
    FROM tournaments t
    LEFT JOIN profit p ON t.tournament_id = p.tournament_id
    WHERE t.status = 'approved' AND t.deleted = 0
";

$filters = [];
if ($name_filter) {
    $filters[] = "t.name LIKE '%$name_filter%'";
}
if ($sport_filter) {
    $filters[] = "t.sport = '$sport_filter'";
}

if ($filters) {
    $query .= " AND " . implode(" AND ", $filters);
}

$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin - Profit Per Tournament</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" />
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
            width: 80%;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-left: 220px; /* Adjust for sidebar */
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
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

        .filter-container {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }

        .filter-container input,
        .filter-container select {
            padding: 10px;
            margin-right: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .filter-container button {
            padding: 10px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .filter-container button:hover {
            background-color: #0056b3;
        }

        .go-back-button {
            display: block;
            width: 100px;
            margin: 20px auto;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            text-align: center;
            text-decoration: none;
            border-radius: 4px;
        }

        .go-back-button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <?php include 'admin_sidebar.php'; ?>
    <div class="container">
        <h2>Profit Per Tournament</h2>
        <div class="filter-container">
            <form action="profit_per_tournament.php" method="get">
                <input type="text" name="name" placeholder="Search by Name" value="<?php echo htmlspecialchars($name_filter); ?>">
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
                <button type="submit">Filter</button>
            </form>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Tournament ID</th>
                    <th>Name</th>
                    <th>Sport</th>
                    <th>Profit Per Tournament</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['tournament_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['sport']); ?></td>
                            <td><?php echo htmlspecialchars($row['profit_per_tournament']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align: center;">No tournaments found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <a href="check_payments.php" class="go-back-button">Go Back</a>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
</body>
</html>
<?php
$conn->close();
?>
