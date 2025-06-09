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
        </style>
</head>
<body>

<h2>All Registered Teams</h2>

<?php while($team = $teams->fetch_assoc()): ?>
    <div class="team-card">
        <h3><?= htmlspecialchars($team['team_name']) ?></h3>
        <p><strong>Manager:</strong> <?= htmlspecialchars($team['manager_name']) ?></p>
        <p><strong>College:</strong> <?= htmlspecialchars($team['college']) ?></p>
          <?php if (!empty($team['logo_url'])): ?>
            <img src="../uploads/<?= $team['logo_url'] ?>" class="logo"><br><br>
        <?php endif; ?>

        <?php if ($is_admin || $is_manager): ?>
            <a href="view_players.php?team_id=<?= $team['id'] ?>" class="btn">View Players</a>