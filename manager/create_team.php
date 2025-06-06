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
  $logo_name = time() . "_" . basename($_FILES["logo"]["name"]);
    $target_file = $upload_dir . $logo_name;
    if (move_uploaded_file($_FILES["logo"]["tmp_name"], $target_file)) {
        $logo_path = $logo_name;
    }
}

$stmt = $conn->prepare("INSERT INTO teams (team_name, user_id, college, logo_url) VALUES (?, ?, ?, ?)");
$stmt->bind_param("siss", $team_name, $user_id, $college, $logo_path);
$stmt->execute();