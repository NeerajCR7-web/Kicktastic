<?php
// admin/managers.php
session_start();
require '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'league_admin') {
    header('Location: ../login.php');
    exit;
}

// Get all users who are team managers
$stmt = $conn->query("
    SELECT u.id, u.name, u.email, t.team_name, t.college, t.logo_url
    FROM users u
    LEFT JOIN teams t ON u.id = t.user_id
    WHERE u.role = 'team_manager'
    ORDER BY u.name ASC
");

$managers = [];
while ($row = $stmt->fetch_assoc()) {
    $managers[] = $row;
}
?>
<?php include '../includes/header.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>All Managers &middot; KickTastic Admin</title>
  <link rel="stylesheet" href="../assets/css/main.css">
  <style>
    body {
      background: #f9f9f9;
    }

    h1 {
      text-align: center;
      margin-bottom: 2rem;
    }

    .cards-container {
      display: flex;
      flex-wrap: wrap;
      gap: 1.5rem;
      justify-content: center;
    }

    .manager-card {
      background: #fff;
      border-radius: 15px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
      padding: 1.5rem;
      width: 280px;
      text-align: center;
    }

    .manager-card img {
      width: 70px;
      height: 70px;
      object-fit: cover;
      border-radius: 50%;
      border: 2px solid #ccc;
      margin-bottom: 0.75rem;
    }

    .manager-card h3 {
      margin-bottom: 0.3rem;
      color: #333;
    }

    .manager-card p {
      margin: 0.2rem 0;
      font-size: 0.9rem;
      color: #555;
    }

    .manager-card .team-name {
      font-weight: bold;
      color: #007bff;
      margin-top: 0.5rem;
    }

    .manager-card .no-team {
      color: #999;
      font-style: italic;
      margin-top: 0.5rem;
    }
  </style>
</head>
<body>

  <h1>All Registered Managers</h1>

  <div class="cards-container">
    <?php foreach ($managers as $m): ?>
      <div class="manager-card">
        <img src="<?= $m['logo_url'] ? '../uploads/' . htmlspecialchars($m['logo_url']) : '../assets/images/logo.png' ?>" alt="Manager Logo">

        <h3><?= htmlspecialchars($m['name']) ?></h3>
        <p><?= htmlspecialchars($m['email']) ?></p>

        <?php if ($m['team_name']): ?>
          <p class="team-name"><?= htmlspecialchars($m['team_name']) ?></p>
          <p><strong>College:</strong> <?= htmlspecialchars($m['college']) ?></p>
        <?php else: ?>
          <p class="no-team">No team created yet</p>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>

</body>
</html>
<?php include '../includes/footer.php'; ?>