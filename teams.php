<?php
require 'includes/db.php';

$teams = $conn->query("SELECT teams.*, users.name as manager_name FROM teams 
                       JOIN users ON teams.user_id = users.id");

function getPlayersByTeam($conn, $team_id) {
    return $conn->query("SELECT * FROM players WHERE team_id = $team_id");
}
?>
<?php include 'includes/public_header.php'; ?>

<!DOCTYPE html>
<html>
<head>
    <title>Teams and Players</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f6f9;
        }

        h2 {
            text-align: center;
            color: #222;
        }

        .team-container {
               display: flex;
            flex-wrap: wrap;
            justify-content: center;
            align-items: flex-start;
            padding: 10px;
        }

        .team-card {
            align-self: flex-start; 
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            width: 300px;
            margin: 15px;
            padding: 20px;
            text-align: center;
            position: relative;
            transition: all 0.3s ease;
        }

        .team-card img {
            width: 100px;
            height: 100px;
            object-fit: contain;
            margin: 10px 0;
        }

        .view-btn {
            background-color: #007bff;
            color: white;
            padding: 10px 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 10px;
            font-weight: bold;
        }

        .players-section {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.4s ease;
            margin-top: 10px;
            text-align: left;
        }

        .players-section.expanded {
            max-height: 1000px; 
        }

        .player-card {
            background: #f1f1f1;
            border-radius: 8px;
            padding: 8px;
            margin-bottom: 8px;
        }

        .player-card span {
            font-weight: bold;
        }
           .no-teams {
            text-align: center;
            font-size: 18px;
            color: #555;
            padding: 50px 20px;
            max-width: 600px;
            margin: 0 auto;
        }

        .no-teams img {
            width: 220px;
            margin-bottom: 20px;
        }
        
    </style>
    <script>
        function togglePlayers(id, btn) {
            const section = document.getElementById('players-' + id);
            section.classList.toggle('expanded');
            btn.innerText = section.classList.contains('expanded') ? 'Hide Players' : 'View Players';
        }
    </script>
</head>
<body>

    <h2>Teams Overview</h2>
    <div class="team-container">
    <?php
    $teamCount = $teams->num_rows;
if ($teamCount === 8) {

        while ($team = $teams->fetch_assoc()) {
            $players = getPlayersByTeam($conn, $team['id']);
            ?>
            <div class="team-card">
                <h3><?= htmlspecialchars($team['team_name']) ?></h3>
                <p><strong>Manager:</strong> <?= htmlspecialchars($team['manager_name']) ?></p>
                <p><strong>College:</strong> <?= htmlspecialchars($team['college']) ?></p>
                <img src="uploads/<?= htmlspecialchars($team['logo_url']) ?>" alt="Team Logo">
                <button class="view-btn" onclick="togglePlayers(<?= $team['id'] ?>, this)">View Players</button>

                <div id="players-<?= $team['id'] ?>" class="players-section">
                    <?php if ($players->num_rows > 0): ?>
                        <?php while ($p = $players->fetch_assoc()): ?>
                            <div class="player-card">
                                <span><?= htmlspecialchars($p['name']) ?></span><br>
                                Position: <?= htmlspecialchars($p['position']) ?><br>
                                Jersey #: <?= htmlspecialchars($p['jersey_number']) ?>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p>No players added.</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php
        }
    } else {
        ?>
        <div class="no-teams">
<img src="https://cdn-icons-png.flaticon.com/512/7486/7486817.png" alt="No Teams" width="220">
            <p><strong>No teams have been registered yet.</strong></p>
            <p>Please check back later once teams have been added to the tournament.</p>
        </div>
    <?php } ?>
</div>


</body>
</html>
<?php include 'includes/public_footer.php'; ?>
