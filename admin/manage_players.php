<?php
// admin/manage_players.php
session_start();
require '../includes/db.php';
if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'league_admin') {
    header('Location: ../index.php');
    exit;
}

$team_id = intval($_GET['team_id'] ?? 0);
if (!$team_id) {
    header('Location: teams.php');
    exit;
}

// fetch team name
$stmt = $conn->prepare("SELECT team_name FROM teams WHERE id = ?");
$stmt->bind_param("i", $team_id);
$stmt->execute();
$stmt->bind_result($team_name);
$stmt->fetch();
$stmt->close();

// handle form actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // update player
    if (isset($_POST['edit_player'])) {
        $pid    = intval($_POST['player_id']);
        $first  = trim($_POST['first_name']);
        $last   = trim($_POST['last_name']);
        $jersey = intval($_POST['jersey_no']);
        $pos    = trim($_POST['position']);
        $full   = $first . ($last !== '' ? " {$last}" : '');
        $u = $conn->prepare("
            UPDATE players
               SET name = ?, jersey_number = ?, position = ?
             WHERE id = ?");
        $u->bind_param("sisi", $full, $jersey, $pos, $pid);
        $u->execute();
        $u->close();
    }
    // delete player
    if (isset($_POST['delete_player'])) {
        $pid = intval($_POST['player_id']);
        $d   = $conn->prepare("DELETE FROM players WHERE id = ?");
        $d->bind_param("i", $pid);
        $d->execute();
        $d->close();
    }
    // add new player
    if (isset($_POST['add_player'])) {
        $first  = trim($_POST['new_first_name']);
        $last   = trim($_POST['new_last_name']);
        $jersey = intval($_POST['new_jersey_no']);
        $pos    = trim($_POST['new_position']);
        $full   = $first . ($last !== '' ? " {$last}" : '');
        $i = $conn->prepare("
            INSERT INTO players (team_id, name, jersey_number, position)
                 VALUES (?, ?, ?, ?)");
        $i->bind_param("isis", $team_id, $full, $jersey, $pos);
        $i->execute();
        $i->close();
    }
    header("Location: manage_players.php?team_id={$team_id}");
    exit;
}

// fetch players
$players = [];
$r = $conn->prepare("
    SELECT id, name, jersey_number, position 
      FROM players 
     WHERE team_id = ?
     ORDER BY jersey_number ASC");
$r->bind_param("i", $team_id);
$r->execute();
$res = $r->get_result();
while ($row = $res->fetch_assoc()) {
    // split name into first/last
    $parts = explode(' ', $row['name'], 2);
    $row['first_name'] = $parts[0];
    $row['last_name']  = $parts[1] ?? '';
    $players[] = $row;
}
$r->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Players · <?= htmlspecialchars($team_name) ?></title>
  <style>
    body { font-family: Arial, sans-serif; background: #f2f4f7; padding: 2rem; }
    h1 { margin-bottom: 1.5rem; color: #333; }
    .card { background: #fff; border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            padding: 1rem; margin-bottom: 2rem; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 0.75rem; border-bottom: 1px solid #e0e0e0; }
    th { background: #fafafa; color: #555; text-align: left; }
    input[type="text"], input[type="number"] {
      width: 100%; padding: 0.5rem; border: 1px solid #ccd0d5;
      border-radius: 4px; font-size: 0.95rem;
    }
    button {
      padding: 0.5rem 0.9rem; font-size: 0.9rem;
      border: none; border-radius: 4px; cursor: pointer;
      transition: background 0.2s;
    }
    .btn-save   { background: #38a169; color: #fff; }
    .btn-save:hover   { background: #2f855a; }
    .btn-delete { background: #e53e3e; color: #fff; margin-left: 0.5rem; }
    .btn-delete:hover { background: #c53030; }
    .add-form label { display: block; margin-top: 0.75rem; color: #444; }
    .add-form button { background: #3182ce; color: white; margin-top: 1rem; }
    .add-form button:hover { background: #2b6cb0; }
    a.back { display: inline-block; margin-top: 1rem; color: #3182ce; }
    a.back:hover { text-decoration: underline; }
  </style>
</head>
<body>

  <h1>Players for “<?= htmlspecialchars($team_name) ?>”</h1>

  <div class="card">
    <table>
      <thead>
        <tr>
          <th>Jersey No.</th>
          <th>First Name</th>
          <th>Last Name</th>
          <th>Position</th>
          <th style="width:160px">Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($players as $p): ?>
        <tr>
          <form method="post">
            <td>
              <input type="number" name="jersey_no"
                     value="<?= $p['jersey_number'] ?>"
                     min="0" required>
            </td>
            <td>
              <input type="text" name="first_name"
                     value="<?= htmlspecialchars($p['first_name']) ?>"
                     required>
            </td>
            <td>
              <input type="text" name="last_name"
                     value="<?= htmlspecialchars($p['last_name']) ?>">
            </td>
            <td>
              <input type="text" name="position"
                     value="<?= htmlspecialchars($p['position']) ?>"
                     required>
            </td>
            <td>
              <input type="hidden" name="player_id" value="<?= $p['id'] ?>">
              <button type="submit" name="edit_player" class="btn-save">Save</button>
              <button type="submit" name="delete_player" class="btn-delete">Delete</button>
            </td>
          </form>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="card add-form">
    <h2>Add New Player</h2>
    <form method="post">
      <label>Jersey No.</label>
      <input type="number" name="new_jersey_no" min="0" required>
      <label>First Name</label>
      <input type="text" name="new_first_name" required>
      <label>Last Name</label>
      <input type="text" name="new_last_name">
      <label>Position</label>
      <input type="text" name="new_position" required>
      <button type="submit" name="add_player">Add Player</button>
    </form>
  </div>

  <a href="teams.php" class="back">← Back to Teams</a>

</body>
</html>
