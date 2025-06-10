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
         body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f6f9;
            margin: 0;
            padding: 20px;
        }

        h2 {
            font-size: 28px;
            font-weight: 600;
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }
        .team-card {
          background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin: 15px;
            width: 300px;
            display: inline-block;
            vertical-align: top;
            text-align: center;
            transition: transform 0.2s ease;
        }
          .team-card:hover {
            transform: translateY(-5px);
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
            <?php endif; ?>
    </div>
    <?php endwhile; ?>

<br><br>
<a href="dashboard.php">Back to Dashboard</a>

</body>
</html>