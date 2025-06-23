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

// Create Team
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

// Add/ Edit Player
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_player'])) {
    $player_id = $_POST['player_id'] ?? null;
    $name = $_POST['name'];
    $position = $_POST['position'];
    $jersey = $_POST['jersey'];
    $image_url = "";

    if (!empty($_FILES['image']['name'])) {
        $upload_dir = "../uploads/";
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $image_name = time() . "_" . basename($_FILES["image"]["name"]);
        $target_file = $upload_dir . $image_name;
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image_url = $image_name;
        }
    }

    if ($player_id) {
        if ($image_url) {
            $stmt = $conn->prepare("UPDATE players SET name=?, position=?, jersey_number=?, image_url=? WHERE id=? AND team_id=?");
            $stmt->bind_param("ssisii", $name, $position, $jersey, $image_url, $player_id, $team['id']);
        } else {
            $stmt = $conn->prepare("UPDATE players SET name=?, position=?, jersey_number=? WHERE id=? AND team_id=?");
            $stmt->bind_param("ssiii", $name, $position, $jersey, $player_id, $team['id']);
        }
    } else {
        if ($player_count < 8) {
            $stmt = $conn->prepare("INSERT INTO players (team_id, name, position, jersey_number, image_url) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("issis", $team['id'], $name, $position, $jersey, $image_url);
        }
    }

    if (isset($stmt)) {
        $stmt->execute();
        header("Location: dashboard.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manager Dashboard</title>
  <link rel="stylesheet" href="../assets/css/main.css"> 
  <style>
    body {
      padding: 2rem;
    }
    .dashboard-container {
      max-width: 960px;
      margin: auto;
      background: #fff;
      padding: 2rem;
      border-radius: 12px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    h2, h3 {
      color: #007bff;
      text-align: center;
      margin-bottom: 1rem;
    }
    .team-info {
      text-align: center;
      margin-bottom: 2rem;
    }
    .team-info img {
      width: 100px;
      height: 100px;
      object-fit: cover;
      border-radius: 50%;
      margin-top: 1rem;
    }
    .btn {
      background: #007bff;
      color: white;
      padding: 8px 14px;
      border-radius: 6px;
      border: none;
      cursor: pointer;
      text-decoration: none;
      margin: 0.3rem;
      font-size: inherit;
    }
    .btn:hover {
      background: #0056b3;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 1.5rem;
    }
    table th, table td {
      padding: 12px;
      border-bottom: 1px solid #ddd;
      text-align: center;
    }
    table th {
      background: #f0f0f0;
    }
    .modal, .overlay {
      display: none;
      position: fixed;
      top: 0; left: 0;
      width: 100%; height: 100%;
      z-index: 1000;
    }
    .overlay {
      background: rgba(0, 0, 0, 0.6);
    }
    .modal-content {
      position: fixed;
      top: 50%; left: 50%;
      transform: translate(-50%, -50%);
      background: #fff;
      padding: 2rem;
      width: 400px;
      border-radius: 10px;
      z-index: 1001;
      box-shadow: 0 2px 10px rgba(0,0,0,0.3);
    }
    input, select {
      width: 100%;
      padding: 0.6rem;
      margin-bottom: 1rem;
      border: 1px solid #ccc;
      border-radius: 6px;
    }
    .user-button {
  background-color: #3498db;
  color: white;
  padding: 0.6rem 1rem;
  border: none;
  border-radius: 6px;
  font-size: 1rem;
  cursor: pointer;
  box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.dropdown-content {
  display: none;
  position: absolute;
  right: 0;
  top: 45px;
  background-color: #ffffff;
  min-width: 160px;
  box-shadow: 0 4px 8px rgba(0,0,0,0.15);
  border-radius: 6px;
  z-index: 999;
  overflow: hidden;
}

.dropdown-content a {
  color: #333;
  padding: 12px 16px;
  text-decoration: none;
  display: block;
  font-size: 0.95rem;
}

.dropdown-content a:hover {
  background-color: #f1f1f1;
}

  </style>
</head>
<body>

<div class="dashboard-container">
  

  <h2>Welcome <?= htmlspecialchars($user['name']) ?> 👋</h2>

  <?php if (!$team): ?>
    <script>window.onload = () => openModal('createTeamModal');</script>
  <?php else: ?>
    <div class="team-info">
      <h3><?= htmlspecialchars($team['college']) ?> Portal</h3>
      <p><strong>Team:</strong> <?= $team['team_name'] ?> | <strong>College:</strong> <?= $team['college'] ?></p>
      <?php if (!empty($team['logo_url'])): ?>
        <img src="../uploads/<?= $team['logo_url'] ?>" alt="Team Logo">
      <?php endif; ?>
      <br><br>
      <button class="btn" onclick="openModal('playerModal')">Manage Players</button>
      <a href="view_teams.php" class="btn" >View All Teams</a>
      <a href="../public_schedule.php" class="btn">View Schedule</a>
      <a href="index.php" class="btn">Home</a>
    </div>

    <h3>Players (<?= $player_count ?>/8)</h3>
    <?php if ($players && $players->num_rows > 0): ?>
      <table>
        <tr><th>Image</th><th>Name</th><th>Position</th><th>Jersey</th><th>Edit</th></tr>
        <?php while ($p = $players->fetch_assoc()): ?>
        <tr>
          <td><?= $p['image_url'] ? "<img src='../uploads/{$p['image_url']}' width='50'>" : 'N/A' ?></td>
          <td><?= htmlspecialchars($p['name']) ?></td>
          <td><?= htmlspecialchars($p['position']) ?></td>
          <td>#<?= $p['jersey_number'] ?></td>
          <td><button class="btn" onclick='editPlayer(<?= json_encode($p) ?>)'>Edit</button></td>
        </tr>
        <?php endwhile; ?>
      </table>
    <?php else: ?>
      <p>No players added yet.</p>
    <?php endif; ?>
  <?php endif; ?>
</div>

<div class="overlay" id="overlay" onclick="closeModals()"></div>

<div class="modal" id="playerModal">
  <div class="modal-content">
    <h3 id="modalTitle">Add/Edit Player</h3>
    <form method="POST" enctype="multipart/form-data">
      <input type="hidden" name="player_id" id="player_id">
      <input type="text" name="name" id="name" placeholder="Player Name" required>
      <input type="text" name="position" id="position" placeholder="Position" required>
      <input type="number" name="jersey" id="jersey" placeholder="Jersey #" required>
      <input type="file" name="image">
      <input type="submit" name="save_player" class="btn" value="Save Player">
      <button type="button" class="btn" style="background:gray;" onclick="closeModals()">Cancel</button>
    </form>
  </div>
</div>

<div class="modal" id="createTeamModal">
  <div class="modal-content">
    <h3>Create Your Team</h3>
    <form method="POST" enctype="multipart/form-data">
      <input type="text" name="team_name" placeholder="Team Name" required>
      <select name="college" required>
        
    <option value="Humber College">Humber College</option>
    <option value="Seneca College">Seneca College</option>
    <option value="George Brown College">George Brown College</option>
    <option value="Centennial College">Centennial College</option>
    <option value="Sheridan College">Sheridan College (Toronto campus)</option>

    <option value="University of Toronto - St. George">University of Toronto - St. George</option>
    <option value="University of Toronto - Scarborough">University of Toronto - Scarborough</option>
    <option value="University of Toronto - Mississauga">University of Toronto - Mississauga</option>
    <option value="York University">York University</option>
    <option value="Toronto Metropolitan University">Toronto Metropolitan University (formerly Ryerson)</option>
    <option value="OCAD University">OCAD University</option>

    <option value="Toronto Film School">Toronto Film School</option>
    <option value="Toronto School of Management">Toronto School of Management</option>
    <option value="Canadian College of Naturopathic Medicine">Canadian College of Naturopathic Medicine</option>
    <option value="Trebas Institute Toronto">Trebas Institute Toronto</option>
    <option value="Canadian Business College">Canadian Business College</option>
    <option value="Evergreen College">Evergreen College</option>
    <option value="Anderson College of Health, Business and Technology">Anderson College</option>
    <option value="CDI College">CDI College - Toronto</option>
    <option value="Herzing College Toronto">Herzing College Toronto</option>
    <option value="ILAC International College">ILAC International College</option>
    <option value="BizTech College">BizTech College</option>
    <option value="National Academy of Health & Business">National Academy of Health & Business</option>

      </select>
      <input type="file" name="logo" required>
      <input type="submit" name="create_team" class="btn" value="Create Team">
      <button type="button" class="btn" style="background:gray;" onclick="closeModals()">Cancel</button>
    </form>
  </div>
</div>

<script>
function openModal(id) {
    document.getElementById("overlay").style.display = "block";
    document.getElementById(id).style.display = "block";
}
function closeModals() {
    document.getElementById("overlay").style.display = "none";
    document.querySelectorAll('.modal').forEach(m => m.style.display = "none");
}
function editPlayer(player) {
    openModal('playerModal');
    document.getElementById("modalTitle").innerText = "Edit Player";
    document.getElementById("player_id").value = player.id;
    document.getElementById("name").value = player.name;
    document.getElementById("position").value = player.position;
    document.getElementById("jersey").value = player.jersey_number;
}
</script>

</body>
</html>
