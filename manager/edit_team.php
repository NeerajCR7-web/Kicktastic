<?php
session_start();
require '../includes/db.php';

if ($_SESSION['role'] !== 'team_manager') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$team = $conn->query("SELECT * FROM teams WHERE user_id = $user_id")->fetch_assoc();