<?php
session_start();
if ($_SESSION['role'] !== 'league_admin') {
    header("Location: ../login.php");
    exit;
}
require '../includes/db.php';

$team_id = $_GET['team_id'] ?? 0;

// Fetch team name
$team = $conn->query("SELECT * FROM teams WHERE id = $team_id")->fetch_assoc();

// Fetch players
$players = $conn->query("SELECT * FROM players WHERE team_id = $team_id");
?>

<h2>Players for <?= htmlspecialchars($team['team_name']) ?></h2>

<?php if ($players->num_rows > 0): ?>
<table border="1" cellpadding="10">
    <tr>
        <th>Image</th>
        <th>Name</th>
        <th>Position</th>
        <th>Jersey #</th>
    </tr>
    <?php while($p = $players->fetch_assoc()): ?>
    <tr>
        <td>
            <?php if ($p['image_url']): ?>
                <img src="../uploads/<?= $p['image_url'] ?>" width="60">
            <?php else: ?>
                No Image
            <?php endif; ?>
        </td>
        <td><?= htmlspecialchars($p['name']) ?></td>
        <td><?= htmlspecialchars($p['position']) ?></td>
        <td>#<?= $p['jersey_number'] ?></td>
    </tr>
    <?php endwhile; ?>
</table>
<?php else: ?>
    <p>No players found for this team.</p>
<?php endif; ?>

<br>
<a href="dashboard.php">← Back to Admin Dashboard</a>
