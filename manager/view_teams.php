<?php
session_start();
require '../includes/db.php';

if (!isset($_SESSION['role'])) {
    header("Location: ../login.php");
    exit;
}
$is_admin = $_SESSION['role'] === 'admin';
$is_manager = $_SESSION['role'] === 'team_manager';