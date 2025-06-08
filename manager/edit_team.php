<?php
session_start();
require '../includes/db.php';

if ($_SESSION['role'] !== 'team_manager') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$team = $conn->query("SELECT * FROM teams WHERE user_id = $user_id")->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $team_name = $_POST['team_name'];
    $college = $_POST['college'];
    $logo_path = $team['logo_url'];

    if (!empty($_FILES['logo']['name'])) {
        $upload_dir = "../uploads/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $logo_name = time() . "_" . basename($_FILES["logo"]["name"]);
        $target_file = $upload_dir . $logo_name;

        if (move_uploaded_file($_FILES["logo"]["tmp_name"], $target_file)) {
            $logo_path = $logo_name;
        }
    }