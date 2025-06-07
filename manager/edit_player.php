<?php
session_start();
require '../includes/db.php';

if ($_SESSION['role'] !== 'team_manager') {
    header("Location: ../login.php");
    exit;
}

$player_id = $_GET['id'];
$player = $conn->query("SELECT * FROM players WHERE id = $player_id")->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $position = $_POST['position'];
    $jersey = $_POST['jersey'];
    $image_url = $player['image_url'];

    
    if (!empty($_FILES['image']['name'])) {
        $upload_dir = "../uploads/";
        $image_name = time() . "_" . basename($_FILES["image"]["name"]);
        $target_file = $upload_dir . $image_name;

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image_url = $image_name;
        }
    }