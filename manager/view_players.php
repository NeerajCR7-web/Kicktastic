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