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
<?php include '../includes/manager_header.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>All Registered Teams</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f6f9;
            margin: 0;
        }

        h2 {
            font-size: 28px;
            font-weight: 600;
            text-align: center;
            margin-bottom: 30px;
            color: #333;
            margin-top: 30px
        }

        .team-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin: 50px;
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
            width: 90px;
            height: 90px;
            object-fit: contain;
            margin-top: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }

        .btn {
            padding: 10px 16px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            display: inline-block;
            margin-top: 12px;
            transition: background 0.3s ease;
        }

        .btn:hover {
            background: #0056b3;
        }

        a.back-link {
            display: block;
            text-align: center;
            margin-top: 40px;
            font-weight: bold;
            text-decoration: none;
            color: #007bff;
        }

        a.back-link:hover {
            text-decoration: underline;
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


</body>
</html>
<?php include '../includes/manager_footer.php'; ?>