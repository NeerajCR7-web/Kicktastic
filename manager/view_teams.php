<?php
session_start();
require '../includes/db.php';

if (!isset($_SESSION['role'])) {
    header("Location: ../login.php");
    exit;
}
$is_admin = $_SESSION['role'] === 'admin';
$is_manager = $_SESSION['role'] === 'team_manager';

// Fetch all teams
$teams = $conn->query("SELECT teams.*, users.name as manager_name FROM teams 
                       JOIN users ON teams.user_id = users.id");
?>

<!DOCTYPE html>
<html>
<head>
    <title>All Registered Teams</title>
    <style>
        .team-card {
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 15px;
            margin: 10px;
            width: 300px;
            display: inline-block;
            vertical-align: top;
            text-align: center;
        }
        img.logo {
            width: 80px;
            height: auto;
        }
         .btn {
            padding: 8px 12px;
            background: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .btn:hover {
            background: #218838;
        }