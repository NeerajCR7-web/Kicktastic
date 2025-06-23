<?php
session_start();
require '../includes/db.php';

$result = $conn->query("SELECT m.id, t1.team_name AS team1, t2.team_name AS team2, m.match_date
                        FROM matches m
                        JOIN teams t1 ON m.team1_id = t1.id
                        JOIN teams t2 ON m.team2_id = t2.id
                        ORDER BY m.match_date ASC");
?>
<h2>Match Schedule</h2>
<table border='1'>
<tr><th>#</th><th>Team 1</th><th>vs</th><th>Team 2</th><th>Date</th></tr>
<?php while($row = $result->fetch_assoc()): ?>
<tr>
    <td><?= $row['id'] ?></td>
    <td><?= $row['team1'] ?></td>
    <td>vs</td>
    <td><?= $row['team2'] ?></td>
    <td><?= $row['match_date'] ?></td>
</tr>
<?php endwhile; ?>
</table>
<a href='dashboard.php'>Back to Dashboard</a>
