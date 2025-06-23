<?php
require 'includes/db.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$teams = $conn->query("SELECT id, team_name FROM teams ORDER BY id ASC");
$allTeams = [];
while ($row = $teams->fetch_assoc()) {
    $allTeams[] = $row;
}

if (count($allTeams) !== 8) {
    echo "Schedule generation requires exactly 8 teams.";
    exit;
}


header("Location: schedule.php?success=1");
exit;
?>
