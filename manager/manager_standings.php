<?php
require '../includes/db.php';

$teams_result = $conn->query("SELECT id, team_name, logo_url FROM teams ORDER BY id ASC");
$all_teams = [];
while ($row = $teams_result->fetch_assoc()) {
    $all_teams[] = $row;
}
if (count($all_teams) !== 8) {
    include '../includes/manager_header.php';
    echo '
    <style>
        .no-teams {
            text-align: center;
            font-size: 18px;
            color: #555;
            padding: 50px 20px;
            max-width: 600px;
            margin: 80px auto;
        }
        .no-teams img {
            width: 220px;
            margin-bottom: 20px;
        }
    </style>
    <div class="no-teams">
        <img src="https://cdn-icons-png.flaticon.com/512/7486/7486817.png" alt="No Teams">
        <p><strong>No teams have been registered yet.</strong></p>
        <p>Please check back later once teams have been added to the tournament.</p>
    </div>';
    include '../includes/manager_footer.php';
    exit;
}

$teams_by_id = [];
foreach ($all_teams as $t) {
    $teams_by_id[$t['id']] = $t;
}

$stored = [];
$res = $conn->query("SELECT * FROM match_results");
while ($r = $res->fetch_assoc()) {
    $stored[$r['match_key']] = $r;
}

$groupA = array_slice($all_teams, 0, 4);
$groupB = array_slice($all_teams, 4, 4);

function get_matches(array $group) {
    $pairs = [];
    for ($i = 0; $i < count($group); $i++) {
        for ($j = $i + 1; $j < count($group); $j++) {
            $pairs[] = [
                'team1' => $group[$i],
                'team2' => $group[$j]
            ];
        }
    }
    return $pairs;
} 

$statsA = [];
foreach ($groupA as $team) {
    $statsA[$team['id']] = [
        'id'      => $team['id'],
        'name'    => $team['team_name'],
        'played'  => 0,
        'won'     => 0,
        'draw'    => 0,
        'lost'    => 0,
        'gf'      => 0,
        'ga'      => 0,
        'points'  => 0,
        'form'    => []  
    ];
}
$statsB = [];
foreach ($groupB as $team) {
    $statsB[$team['id']] = [
        'id'      => $team['id'],
        'name'    => $team['team_name'],
        'played'  => 0,
        'won'     => 0,
        'draw'    => 0,
        'lost'    => 0,
        'gf'      => 0,
        'ga'      => 0,
        'points'  => 0,
        'form'    => []
    ];
}

$groupA_matches = get_matches($groupA); // 6 pairings
for ($i = 0; $i < count($groupA_matches); $i++) {
    $pair  = $groupA_matches[$i];
    $t1    = $pair['team1'];
    $t2    = $pair['team2'];
    $key   = "A" . $i;
    if (!isset($stored[$key])) continue;
    $r     = $stored[$key];
    $s1    = intval($r['score1']);
    $s2    = intval($r['score2']);

    $statsA[$t1['id']]['played']++;
    $statsA[$t2['id']]['played']++;
    $statsA[$t1['id']]['gf'] += $s1;
    $statsA[$t1['id']]['ga'] += $s2;
    $statsA[$t2['id']]['gf'] += $s2;
    $statsA[$t2['id']]['ga'] += $s1;

    if ($s1 > $s2) {
        $statsA[$t1['id']]['won']++;
        $statsA[$t2['id']]['lost']++;
        $statsA[$t1['id']]['points'] += 3;
        $statsA[$t1['id']]['form'][] = 'W';
        $statsA[$t2['id']]['form'][] = 'L';
    } elseif ($s1 < $s2) {
        $statsA[$t2['id']]['won']++;
        $statsA[$t1['id']]['lost']++;
        $statsA[$t2['id']]['points'] += 3;
        $statsA[$t1['id']]['form'][] = 'L';
        $statsA[$t2['id']]['form'][] = 'W';
    } else {
        $statsA[$t1['id']]['draw']++;
        $statsA[$t2['id']]['draw']++;
        $statsA[$t1['id']]['points'] += 1;
        $statsA[$t2['id']]['points'] += 1;
        $statsA[$t1['id']]['form'][] = 'D';
        $statsA[$t2['id']]['form'][] = 'D';
    }
}
foreach ($statsA as &$rowA) {
    $rowA['gd'] = $rowA['gf'] - $rowA['ga'];
}
unset($rowA);
usort($statsA, function($a, $b) {
    if ($a['points'] !== $b['points']) return $b['points'] - $a['points'];
    if ($a['gd'] !== $b['gd']) return $b['gd'] - $a['gd'];
    return $b['gf'] - $a['gf'];
});

$groupB_matches = get_matches($groupB);
for ($i = 0; $i < count($groupB_matches); $i++) {
    $pair  = $groupB_matches[$i];
    $t1    = $pair['team1'];
    $t2    = $pair['team2'];
    $key   = "B" . $i;
    if (!isset($stored[$key])) continue;
    $r     = $stored[$key];
    $s1    = intval($r['score1']);
    $s2    = intval($r['score2']);

    $statsB[$t1['id']]['played']++;
    $statsB[$t2['id']]['played']++;
    $statsB[$t1['id']]['gf'] += $s1;
    $statsB[$t1['id']]['ga'] += $s2;
    $statsB[$t2['id']]['gf'] += $s2;
    $statsB[$t2['id']]['ga'] += $s1;

    if ($s1 > $s2) {
        $statsB[$t1['id']]['won']++;
        $statsB[$t2['id']]['lost']++;
        $statsB[$t1['id']]['points'] += 3;
        $statsB[$t1['id']]['form'][] = 'W';
        $statsB[$t2['id']]['form'][] = 'L';
    } elseif ($s1 < $s2) {
        $statsB[$t2['id']]['won']++;
        $statsB[$t1['id']]['lost']++;
        $statsB[$t2['id']]['points'] += 3;
        $statsB[$t1['id']]['form'][] = 'L';
        $statsB[$t2['id']]['form'][] = 'W';
    } else {
        $statsB[$t1['id']]['draw']++;
        $statsB[$t2['id']]['draw']++;
        $statsB[$t1['id']]['points'] += 1;
        $statsB[$t2['id']]['points'] += 1;
        $statsB[$t1['id']]['form'][] = 'D';
        $statsB[$t2['id']]['form'][] = 'D';
    }
}
foreach ($statsB as &$rowB) {
    $rowB['gd'] = $rowB['gf'] - $rowB['ga'];
}
unset($rowB);
usort($statsB, function($a, $b) {
    if ($a['points'] !== $b['points']) return $b['points'] - $a['points'];
    if ($a['gd'] !== $b['gd']) return $b['gd'] - $a['gd'];
    return $b['gf'] - $a['gf'];
});

$grpA_win   = $statsA[0]['id'];
$grpA_ru    = $statsA[1]['id'];
$grpB_win   = $statsB[0]['id'];
$grpB_ru    = $statsB[1]['id'];

$sf1_team1 = $teams_by_id[$grpA_win];
$sf1_team2 = $teams_by_id[$grpB_ru];
$sf2_team1 = $teams_by_id[$grpB_win];
$sf2_team2 = $teams_by_id[$grpA_ru];

$sf1_res = isset($stored['SF1']) ? $stored['SF1'] : null;
$sf2_res = isset($stored['SF2']) ? $stored['SF2'] : null;

$final_team1 = null;
$final_team2 = null;
if ($sf1_res && $sf2_res) {
    $final_team1 = (intval($sf1_res['score1']) > intval($sf1_res['score2']))
                   ? $sf1_team1 : $sf1_team2;
    $final_team2 = (intval($sf2_res['score1']) > intval($sf2_res['score2']))
                   ? $sf2_team1 : $sf2_team2;
}
$final_res = ($final_team1 && $final_team2 && isset($stored['F1'])) 
              ? $stored['F1'] 
              : null;

?>
<?php include '../includes/manager_header.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Standings</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
            margin: 0;
           
        }
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }
        .group-container {
            margin-bottom: 60px;
        }
        .group-title {
            background-color: #3366CC;
            color: white;
            padding: 12px 20px;
            border-radius: 8px 8px 0 0;
            font-size: 1.2em;
        }
        .standings-table {
            width: 100%;
            border-collapse: collapse;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
        .standings-table th {
            background-color: #000;
            color: white;
            padding: 10px;
            text-align: center;
            font-size: 0.95em;
        }
        .standings-table td {
            background-color: #fff;
            color: #333;
            padding: 10px;
            text-align: center;
            font-size: 0.9em;
            border-bottom: 1px solid #ddd;
        }
        .standings-table tr:nth-child(even) td {
            background-color: #f9f9f9;
        }
        .logo-cell img {
            vertical-align: middle;
            width: 28px;
            height: 28px;
            object-fit: cover;
            border-radius: 50%;
        }
        .footer-link {
            text-align: center;
            margin-top: 10px;
        }
        .footer-link a {
            display: inline-block;
            background-color: #3366CC;
            color: white;
            padding: 8px 18px;
            border-radius: 20px;
            text-decoration: none;
            font-size: 0.9em;
        }
        .footer-link a:hover {
            background-color: #254A9D;
        }
        .knockout-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
        .knockout-title {
            font-size: 1.3em;
            margin-bottom: 15px;
            color: #333;
        }
        .knockout-box {
            margin: 12px 0;
            padding: 12px;
            background: #f9f9f9;
            border-left: 6px solid #007bff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .knockout-box img {
            vertical-align: middle;
            margin-right: 6px;
            width: 28px;
            height: 28px;
            object-fit: cover;
            border-radius: 50%;
        }
    </style>
</head>
<body>

    <h1>GROUP STANDINGS</h1>

    <!-- ========== GROUP A STANDINGS ========== -->
    <div class="group-container">
        <div class="group-title">Group A Standings</div>
        <table class="standings-table">
            <thead>
                <tr>
                    <th>Pos</th>
                    <th>Logo</th>
                    <th>Team</th>
                    <th>Played</th>
                    <th>Won</th>
                    <th>Drawn</th>
                    <th>Lost</th>
                    <th>GF</th>
                    <th>GA</th>
                    <th>GD</th>
                    <th>Points</th>
                    <th>Form</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($statsA as $idx => $row): ?>
                    <tr>
                        <td><?= $idx + 1 ?></td>
                        <td class="logo-cell">
                            <?php
                               
                                $tid = $row['id'];
                                echo "<img src='../uploads/{$teams_by_id[$tid]['logo_url']}' alt='logo'>";
                            ?>
                        </td>
                        <td><?= htmlspecialchars($row['name'], ENT_QUOTES) ?></td>
                        <td><?= intval($row['played']) ?></td>
                        <td><?= intval($row['won']) ?></td>
                        <td><?= intval($row['draw']) ?></td>
                        <td><?= intval($row['lost']) ?></td>
                        <td><?= intval($row['gf']) ?></td>
                        <td><?= intval($row['ga']) ?></td>
                        <td><?= intval($row['gd']) ?></td>
                        <td><?= intval($row['points']) ?></td>
                        <td>
                            <?= implode(' ', $row['form']) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
    </div>

    <div class="group-container">
        <div class="group-title">Group B Standings</div>
        <table class="standings-table">
            <thead>
                <tr>
                    <th>Pos</th>
                    <th>Logo</th>
                    <th>Team</th>
                    <th>Played</th>
                    <th>Won</th>
                    <th>Drawn</th>
                    <th>Lost</th>
                    <th>GF</th>
                    <th>GA</th>
                    <th>GD</th>
                    <th>Points</th>
                    <th>Form</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($statsB as $idx => $row): ?>
                    <tr>
                        <td><?= $idx + 1 ?></td>
                        <td class="logo-cell">
                            <?php
                                $tid = $row['id'];
                                echo "<img src='../uploads/{$teams_by_id[$tid]['logo_url']}' alt='logo'>";
                            ?>
                        </td>
                        <td><?= htmlspecialchars($row['name'], ENT_QUOTES) ?></td>
                        <td><?= intval($row['played']) ?></td>
                        <td><?= intval($row['won']) ?></td>
                        <td><?= intval($row['draw']) ?></td>
                        <td><?= intval($row['lost']) ?></td>
                        <td><?= intval($row['gf']) ?></td>
                        <td><?= intval($row['ga']) ?></td>
                        <td><?= intval($row['gd']) ?></td>
                        <td><?= intval($row['points']) ?></td>
                        <td>
                            <?= implode(' ', $row['form']) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
       
    </div>

    <div class="knockout-container">
    <div class="knockout-title">Knockout Stage</div>

<?php
$group_matches_played = 0;
foreach ($groupA_matches as $i => $match) {
    if (isset($stored["A$i"])) $group_matches_played++;
}
foreach ($groupB_matches as $i => $match) {
    if (isset($stored["B$i"])) $group_matches_played++;
}
?>

<?php if ($group_matches_played < 12): ?>
    <div style="
        max-width: 600px;
        margin: 20px auto;
        padding: 20px;
        border-radius: 12px;
        background-color: #fff3cd;
        border: 1px solid #ffeeba;
        color: #856404;
        font-size: 16px;
        font-family: Arial, sans-serif;
        text-align: center;
    ">
        <strong>Notice:</strong><br>
        Knockout stage matches will appear once all group stage matches are completed.
    </div>

<?php else: ?>

    <div class="knockout-box">
        <strong>Semifinal 1:</strong><br>
        <img src="uploads/<?= $sf1_team1['logo_url'] ?>" width="30"> <?= htmlspecialchars($sf1_team1['team_name']) ?>
        vs
        <img src="uploads/<?= $sf1_team2['logo_url'] ?>" width="30"> <?= htmlspecialchars($sf1_team2['team_name']) ?><br>
        <em>Date:</em> <?= (new DateTime('2025-08-07'))->format('l, F j, Y') ?> at 17:00<br>
        <?php if ($sf1_res): ?>
            <strong>Score:</strong> <?= $sf1_res['score1'] ?> - <?= $sf1_res['score2'] ?><br>
            <strong>MOTM:</strong> <?= $sf1_res['motm'] ?><br>
            <strong>Goalscorers:</strong> <?= $sf1_res['goalscorers'] ?><br>
            <a href="<?= $sf1_res['highlight_url'] ?>" target="_blank">Watch Highlights</a>
        <?php else: ?>
            <em>Not Played Yet</em>
        <?php endif; ?>
    </div>

    <div class="knockout-box">
        <strong>Semifinal 2:</strong><br>
        <img src="uploads/<?= $sf2_team1['logo_url'] ?>" width="30"> <?= htmlspecialchars($sf2_team1['team_name']) ?>
        vs
        <img src="uploads/<?= $sf2_team2['logo_url'] ?>" width="30"> <?= htmlspecialchars($sf2_team2['team_name']) ?><br>
        <em>Date:</em> <?= (new DateTime('2025-08-07'))->format('l, F j, Y') ?> at 19:00<br>
        <?php if ($sf2_res): ?>
            <strong>Score:</strong> <?= $sf2_res['score1'] ?> - <?= $sf2_res['score2'] ?><br>
            <strong>MOTM:</strong> <?= $sf2_res['motm'] ?><br>
            <strong>Goalscorers:</strong> <?= $sf2_res['goalscorers'] ?><br>
            <a href="<?= $sf2_res['highlight_url'] ?>" target="_blank">Watch Highlights</a>
        <?php else: ?>
            <em>Not Played Yet</em>
        <?php endif; ?>
    </div>

    <?php if ($final_team1 && $final_team2): ?>
    <div class="knockout-box">
        <strong>Final:</strong><br>
        <img src="uploads/<?= $final_team1['logo_url'] ?>" width="30"> <?= htmlspecialchars($final_team1['team_name']) ?>
        vs
        <img src="uploads/<?= $final_team2['logo_url'] ?>" width="30"> <?= htmlspecialchars($final_team2['team_name']) ?><br>
        <em>Date:</em> <?= (new DateTime('2025-08-08'))->format('l, F j, Y') ?> at 18:00<br>
        <?php if ($final_res): ?>
            <strong>Score:</strong> <?= $final_res['score1'] ?> - <?= $final_res['score2'] ?><br>
            <strong>MOTM:</strong> <?= $final_res['motm'] ?><br>
            <strong>Goalscorers:</strong> <?= $final_res['goalscorers'] ?><br>
            <a href="<?= $final_res['highlight_url'] ?>" target="_blank">Watch Highlights</a>
        <?php else: ?>
            <em>Not Played Yet</em>
        <?php endif; ?>
    </div>
    <?php endif; ?>
<?php endif; ?>
</div>

</body>
</html>
<?php include '../includes/manager_footer.php'; ?>
