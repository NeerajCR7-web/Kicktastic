<?php
session_start();
require '../includes/db.php';

// Check login and role
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Check if team_id is passed
if (!isset($_GET['team_id'])) {
    echo "No team selected.";
    exit;
}

$team_id = intval($_GET['team_id']);

// Fetch team info
$team_result = $conn->query("SELECT t.team_name, u.name AS manager_name, u.id AS manager_id 
                             FROM teams t JOIN users u ON t.user_id = u.id 
                             WHERE t.id = $team_id");
$team = $team_result->fetch_assoc();

if (!$team) {
    echo "Team not found.";
    exit;
}

$can_edit = ($role === 'admin') || ($role === 'team_manager' && $team['manager_id'] == $user_id);
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Players</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f6f9;
            padding: 20px;
            color: #333;
        }

        h2 {
            font-size: 26px;
            margin-bottom: 10px;
        }

        p {
            font-size: 16px;
            margin-bottom: 20px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            background: #fff;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            border-radius: 10px;
            overflow: hidden;
        }

        th, td {
            padding: 12px 15px;
            text-align: center;
            border-bottom: 1px solid #eee;
        }

        th {
            background-color: #007bff;
            color: white;
            font-weight: bold;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        img {
            border-radius: 6px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        a.edit-link {
            text-decoration: none;
            color: #007bff;
            font-weight: bold;
        }

        a.edit-link:hover {
            text-decoration: underline;
        }

        .back-link {
            display: inline-block;
            margin-top: 30px;
            text-decoration: none;
            color: #007bff;
            font-weight: bold;
        }

        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<h2>Team Name: <?= htmlspecialchars($team['team_name']) ?></h2>
<p>Manager: <?= htmlspecialchars($team['manager_name']) ?></p>

<?php
$players_result = $conn->query("SELECT * FROM players WHERE team_id = $team_id");

if ($players_result->num_rows > 0) {
    echo "<table>";
    echo "<tr><th>Image</th><th>Name</th><th>Position</th><th>Jersey #</th>";
    if ($can_edit) echo "<th>Edit</th>";
    echo "</tr>";

    while ($player = $players_result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>";
        if (!empty($player['image_url'])) {
            echo "<img src='../uploads/" . htmlspecialchars($player['image_url']) . "' width='50'>";
        } else {
            echo "No Image";
        }
        echo "</td>";
        echo "<td>" . htmlspecialchars($player['name']) . "</td>";
        echo "<td>" . htmlspecialchars($player['position']) . "</td>";
        echo "<td>#" . htmlspecialchars($player['jersey_number']) . "</td>";
        if ($can_edit) {
            echo "<td><a href='edit_player.php?id=" . $player['id'] . "' class='edit-link'>🖉 Edit</a></td>";
        }
        echo "</tr>";
    }

    echo "</table>";
} else {
    echo "<p>No players found for this team.</p>";
}
?>

<a href="view_teams.php" class="back-link">← Back to Teams</a>

</body>
</html>
