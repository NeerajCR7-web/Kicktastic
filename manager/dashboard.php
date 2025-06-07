<?php
session_start();
require '../includes/db.php';

if ($_SESSION['role'] !== 'team_manager') {
    header("Location: ../login.php");
    exit;
}
$user_id = $_SESSION['user_id'];
$user = $conn->query("SELECT name FROM users WHERE id = $user_id")->fetch_assoc();
$team = $conn->query("SELECT * FROM teams WHERE user_id = $user_id")->fetch_assoc();

$players = $team ? $conn->query("SELECT * FROM players WHERE team_id = {$team['id']}") : null;
$player_count = $players ? $players->num_rows : 0;

// Handle team creation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_team'])) {
    $team_name = $_POST['team_name'];
    $college = $_POST['college'];
    $logo_url = "";

       if (!empty($_FILES['logo']['name'])) {
        $upload_dir = "../uploads/";
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $image_name = time() . "_" . basename($_FILES["logo"]["name"]);
        $target_file = $upload_dir . $image_name;

        if (move_uploaded_file($_FILES["logo"]["tmp_name"], $target_file)) {
            $logo_url = $image_name;
        }
    }

     $stmt = $conn->prepare("INSERT INTO teams (user_id, team_name, college, logo_url) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $team_name, $college, $logo_url);
    $stmt->execute();
    header("Location: dashboard.php");
    exit;
}