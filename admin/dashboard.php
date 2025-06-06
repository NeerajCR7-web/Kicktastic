<?php
session_start();
if ($_SESSION['role'] !== 'league_admin') {
    header("Location: ../login.php");
    exit;
}
require '../includes/db.php';

$teams = $conn->query("SELECT * FROM teams");
?>

<h2>Admin Dashboard</h2>
<h2>Welcome Admin</h2>

<a href="schedule.php" style="padding:10px 20px; background:#007bff; color:white; text-decoration:none; border-radius:5px;">🏆 View Tournament Schedule</a>
<a href="../public_schedule.php" class="btn">View Public Schedule</a>
<a href="../standings.php">View Standings</a>