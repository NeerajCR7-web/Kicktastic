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