<?php
session_start();
require_once 'db.php';

// Ensure the user is logged in and has the correct role
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'client') {
    header('Location: index.php');
    exit();
}

// Get the team ID from the query string
if (!isset($_GET['team_id']) || !is_numeric($_GET['team_id'])) {
    header('Location: view_registered_teams.php');
    exit();
}

$team_id = intval($_GET['team_id']);

// Fetch the current details of the team
$stmt = $conn->prepare("SELECT team_name, leader_name, leader_contact, members FROM teams WHERE team_id = ? AND registered_by = ?");
$stmt->bind_param("ii", $team_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: view_registered_teams.php');
    exit();
}

$team = $result->fetch_assoc();

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Team Details</title>
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
            color: #fff;
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

        .content-container {
            margin-left: 220px; /* Space for sidebar */
            margin-right: 20px; /* Right margin */
            padding: 20px;
            display: flex;
            justify-content: center;
        }

        .form-container {
            margin-left: 350px; 
            width: 600px;
            max-width: 800px; /* Adjust max-width as needed */
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .form-container h2 {
            text-align: center;
            margin-top: 0;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
        }

        .form-group input, .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            text-align: center;
            margin-top: 10px;
            border: none;
            cursor: pointer;
        }

        .btn:hover {
            background-color: #0056b3;
        }

        .checkbox-group {
            margin-bottom: 20px;
        }

        .checkbox-group label {
            display: block;
            margin-bottom: 5px;
            cursor: pointer;
        }

        .form-group.hidden {
            display: none;
        }

        .btn-go-back {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            text-align: center;
            margin-top: 20px;
            border: none;
            cursor: pointer;
        }

        .btn-go-back:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
    <?php include 'client_sidebar.php'; ?>
    <div class="content-container">
        <div class="form-container">
            <h2>Edit Team Details</h2>
            <form id="editTeamForm" action="update_team_details.php" method="post">
                <input type="hidden" name="team_id" value="<?php echo htmlspecialchars($team_id); ?>">

                <div class="form-group checkbox-group">
                    <label><input type="checkbox" class="update-field" name="update_fields[]" value="team_name"> Team Name</label>
                    <label><input type="checkbox" class="update-field" name="update_fields[]" value="leader_name"> Captain Name</label>
                    <label><input type="checkbox" class="update-field" name="update_fields[]" value="leader_contact"> Captain Contact</label>
                    <label><input type="checkbox" class="update-field" name="update_fields[]" value="members"> Members</label>
                </div>

                <div class="form-group hidden" id="team_name_group">
                    <label for="team_name">Team Name</label>
                    <input type="text" id="team_name" name="team_name" value="<?php echo htmlspecialchars($team['team_name']); ?>">
                </div>

                <div class="form-group hidden" id="leader_name_group">
                    <label for="leader_name">Captain Name</label>
                    <input type="text" id="leader_name" name="leader_name" value="<?php echo htmlspecialchars($team['leader_name']); ?>">
                </div>

                <div class="form-group hidden" id="leader_contact_group">
                    <label for="leader_contact">Captain Contact</label>
                    <input type="text" id="leader_contact" name="leader_contact" value="<?php echo htmlspecialchars($team['leader_contact']); ?>">
                </div>

                <div class="form-group hidden" id="members_group">
                    <label for="members">Members</label>
                    <textarea id="members" name="members"><?php echo htmlspecialchars($team['members']); ?></textarea>
                </div>

                <button type="submit" class="btn">Update Details</button>

                <div id="error-message" class="error-message"></div>
            </form>

            <!-- Go Back Button -->
            <a href="view_registered_teams.php" class="btn-go-back">Go Back</a>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const checkboxes = document.querySelectorAll('.update-field');
            const formGroups = {
                team_name: document.getElementById('team_name_group'),
                leader_name: document.getElementById('leader_name_group'),
                leader_contact: document.getElementById('leader_contact_group'),
                members: document.getElementById('members_group')
            };

            function toggleFormFields() {
                checkboxes.forEach(checkbox => {
                    const field = checkbox.value;
                    if (checkbox.checked) {
                        formGroups[field].classList.remove('hidden');
                    } else {
                        formGroups[field].classList.add('hidden');
                    }
                });
            }

            // Add event listener to checkboxes
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', toggleFormFields);
            });

            // Trigger the toggle function on page load to ensure the correct fields are shown/hidden based on pre-checked checkboxes
            toggleFormFields();

            // Form validation
            document.getElementById('editTeamForm').addEventListener('submit', function(event) {
                const checkedCheckboxes = document.querySelectorAll('.update-field:checked');
                const errorMessage = document.getElementById('error-message');

                if (checkedCheckboxes.length === 0) {
                    event.preventDefault(); // Prevent form submission
                    errorMessage.textContent = 'Please select at least one attribute to update.';
                } else {
                    errorMessage.textContent = ''; // Clear any previous error messages
                }
            });
        });
    </script>
</body>
</html>
