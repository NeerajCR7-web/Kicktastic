<?php
session_start();
if ($_SESSION['role'] !== 'league_admin') {
    header("Location: ../login.php");
    exit;
}
require '../includes/db.php';

$teams = $conn->query("SELECT * FROM teams");
?>

<h2>Admin Dashboard</h2>
<h2>Welcome Admin</h2>

<!-- Add this below your header or existing admin links -->
<a href="schedule.php" style="padding:10px 20px; background:#007bff; color:white; text-decoration:none; border-radius:5px;">🏆 View Tournament Schedule</a>
<a href="../public_schedule.php" class="btn">View Public Schedule</a>
<a href="../standings.php">View Standings</a>



<h3>Registered Teams</h3>

<table border="1" cellpadding="10" cellspacing="0">
    <tr>
        <th>Team Name</th>
        <th>College</th>
        <th>Logo</th>
        <th>Players</th>
    </tr>

    <?php while($team = $teams->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($team['team_name']) ?></td>
            <td><?= htmlspecialchars($team['college']) ?></td>
            <td>
                <?php if (!empty($team['logo_url'])): ?>
                    <img src="../uploads/<?= $team['logo_url'] ?>" width="100">
                <?php else: ?>
                    No Logo
                <?php endif; ?>
            </td>
            <td>
                <a href="view_players.php?team_id=<?= $team['id'] ?>">View Players</a>
            </td>
        </tr>
    <?php endwhile; ?>
</table>

<br>
<a href="../logout.php">Logout</a>
