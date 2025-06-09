<?php
session_start();
require '../includes/db.php';

if (!isset($_SESSION['role'])) {
    header("Location: ../login.php");
    exit;
}
$is_admin = $_SESSION['role'] === 'admin';
$is_manager = $_SESSION['role'] === 'team_manager';

// Fetch all teams
$teams = $conn->query("SELECT teams.*, users.name as manager_name FROM teams 
                       JOIN users ON teams.user_id = users.id");
?>