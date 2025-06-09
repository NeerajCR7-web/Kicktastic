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