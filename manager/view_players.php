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
"<h2>Players from Team: " . htmlspecialchars($team['team_name']) . "</h2>";
echo "<p>Manager: " . htmlspecialchars($team['manager_name']) . "</p>";

// Check if current user is allowed to edit (admin or this team's manager)
$can_edit = ($role === 'admin') || ($role === 'team_manager' && $team['manager_id'] == $user_id);

// Fetch players
$players_result = $conn->query("SELECT * FROM players WHERE team_id = $team_id");

if ($players_result->num_rows > 0) {
    echo "<table border='1' cellpadding='10'>";
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
            echo "<td><a href='edit_player.php?id=" . $player['id'] . "'>🖉 Edit</a></td>";
        }
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No players found for this team.</p>";
}
echo "<br><a href='view_teams.php'>Back to Teams</a>";
?>