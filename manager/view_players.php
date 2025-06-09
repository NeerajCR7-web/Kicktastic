<?php
session_start();
require '../includes/db.php';

// Check login and role
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: ../login.php");
    exit;
}
