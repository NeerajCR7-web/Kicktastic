<?php
session_start();
require '../includes/db.php';

if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'league_admin') {
    header('Location: ../index.php');
    exit;
}

// Fetch all teams
$teams = [];
$res = $conn->query("SELECT teams.id, teams.team_name, teams.logo_url, users.name AS manager_name 
                     FROM teams 
                     JOIN users ON teams.user_id = users.id 
                     ORDER BY teams.id ASC");

while ($row = $res->fetch_assoc()) {
    $teams[] = $row;
}
?>
<?php include '../includes/header.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Teams &middot; KickTastic Admin</title>
  <style>
    body { font-family: Arial, sans-serif;  background: #f9f9f9; }
    h1 { margin-bottom: 1rem; }
    table { width: 100%; border-collapse: collapse; background: #fff; }
    th, td { padding: 0.75rem; text-align: left; border-bottom: 1px solid #ddd; }
    img { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; }
    a.button { display: inline-block; padding: 0.4rem 0.8rem; background: #007bff; color: #fff; border-radius: 4px; text-decoration: none; margin-right: 0.5rem; }
    a.button:hover { background: #0056b3; }
  </style>
</head>
<body>
  <h1>Teams</h1>
  <table>
    <thead>
      <tr>
        <th>Logo</th>
        <th>Name</th>
        <th>Manager</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($teams as $t): ?>
      <tr>
        <td><img src="../uploads/<?= htmlspecialchars($t['logo_url']) ?>" alt=""></td>
        <td><?= htmlspecialchars($t['team_name']) ?></td>
         <td><?= htmlspecialchars($t['manager_name']) ?></td>
        <td>
          <a href="edit_team.php?id=<?= $t['id'] ?>" class="button">Edit Team</a>
          <a href="manage_players.php?team_id=<?= $t['id'] ?>" class="button">Manage Players</a>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</body>
</html>
<?php include '../includes/footer.php'; ?>