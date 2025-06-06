<?php
session_start();
require '../includes/db.php';

$team_name = $_POST['team_name'];
$college = $_POST['college'];
$user_id = $_SESSION['user_id'];
$logo_path = "";
// Handle logo upload if present
if (!empty($_FILES['logo']['name'])) {
    $upload_dir = "../uploads/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true); // create uploads folder if it doesn't exist
    }
