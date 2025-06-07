<?php
session_start();
require '../includes/db.php';

if ($_SESSION['role'] !== 'team_manager') {
    header("Location: ../login.php");
    exit;
}

$player_id = $_GET['id'];
$player = $conn->query("SELECT * FROM players WHERE id = $player_id")->fetch_assoc();