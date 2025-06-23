<?php
// public_schedule.php
require 'includes/db.php';

// -----------------------------------------------------
// 1) FETCH ALL TEAMS (ordered by id ASC to match schedule.php)
// -----------------------------------------------------
$teams_result = $conn->query("SELECT id, team_name, logo_url FROM teams ORDER BY id ASC");
$all_teams = [];
while ($row = $teams_result->fetch_assoc()) {
    $all_teams[] = $row;
}
$team_count = count($all_teams);

if ($team_count !== 8) {
    include 'includes/public_header.php';
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
    include 'includes/public_footer.php';
    exit;
}


// Build a lookup array by team ID
$teams_by_id = [];
foreach ($all_teams as $t) {
    $teams_by_id[$t['id']] = $t;
}

// -----------------------------------------------------
// 2) FETCH ALL SAVED match_results INTO $stored[key]
// -----------------------------------------------------
$stored = [];
$res = $conn->query("SELECT * FROM match_results");
while ($r = $res->fetch_assoc()) {
    $stored[$r['match_key']] = $r;
}

// -----------------------------------------------------
// 3) SPLIT INTO GROUP A & GROUP B (first 4 vs next 4)
// -----------------------------------------------------
$groupA = array_slice($all_teams, 0, 4);
$groupB = array_slice($all_teams, 4, 4);

// -----------------------------------------------------
// 4) HELPER: generate 6 round-robin pairings for a group of 4
// -----------------------------------------------------
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
    return $pairs; // 6 pairings in total
}

// -----------------------------------------------------
// 5) COMPUTE STANDINGS FOR EACH GROUP (to seed semifinals)
// -----------------------------------------------------
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
        'form'    => []  // chronological 'W','D','L'
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
$groupB_matches = get_matches($groupB); // 6 pairings

// Process Group A results
for ($i = 0; $i < count($groupA_matches); $i++) {
    $pair = $groupA_matches[$i];
    $t1   = $pair['team1'];
    $t2   = $pair['team2'];
    $key  = "A" . $i;
    if (! isset($stored[$key])) continue;
    $r    = $stored[$key];
    $s1   = intval($r['score1']);
    $s2   = intval($r['score2']);

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
    if ($a['points'] !== $b['points']) {
        return $b['points'] - $a['points'];
    }
    if ($a['gd'] !== $b['gd']) {
        return $b['gd'] - $a['gd'];
    }
    return $b['gf'] - $a['gf'];
});

// Process Group B results
for ($i = 0; $i < count($groupB_matches); $i++) {
    $pair = $groupB_matches[$i];
    $t1   = $pair['team1'];
    $t2   = $pair['team2'];
    $key  = "B" . $i;
    if (! isset($stored[$key])) continue;
    $r    = $stored[$key];
    $s1   = intval($r['score1']);
    $s2   = intval($r['score2']);

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
    if ($a['points'] !== $b['points']) {
        return $b['points'] - $a['points'];
    }
    if ($a['gd'] !== $b['gd']) {
        return $b['gd'] - $a['gd'];
    }
    return $b['gf'] - $a['gf'];
});

// -----------------------------------------------------
// 6) DETERMINE SEMIFINALISTS (TOP 2 in each group)
// -----------------------------------------------------
$groupA_winner   = $statsA[0]['id'];
$groupA_runnerup = $statsA[1]['id'];
$groupB_winner   = $statsB[0]['id'];
$groupB_runnerup = $statsB[1]['id'];

$sf1_team1 = $teams_by_id[$groupA_winner];
$sf1_team2 = $teams_by_id[$groupB_runnerup];
$sf2_team1 = $teams_by_id[$groupB_winner];
$sf2_team2 = $teams_by_id[$groupA_runnerup];

$sf1_res = isset($stored['SF1']) ? $stored['SF1'] : null;
$sf2_res = isset($stored['SF2']) ? $stored['SF2'] : null;

// If both semis have been played, determine finalists:
$final_team1 = null;
$final_team2 = null;
if ($sf1_res && $sf2_res) {
    if (intval($sf1_res['score1']) > intval($sf1_res['score2'])) {
        $final_team1 = $sf1_team1;
    } else {
        $final_team1 = $sf1_team2;
    }
    if (intval($sf2_res['score1']) > intval($sf2_res['score2'])) {
        $final_team2 = $sf2_team1;
    } else {
        $final_team2 = $sf2_team2;
    }
}
$final_res = ($final_team1 && $final_team2 && isset($stored['F1'])) 
              ? $stored['F1'] 
              : null;

// -----------------------------------------------------
// 7) HTML OUTPUT BEGINS
// -----------------------------------------------------
?>
<?php include 'includes/public_header.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Public Tournament Schedule</title>
    <style>
        body { font-family: Arial, sans-serif;  background: #f9f9f9; }
        h2 { margin-top: 40px; color: #333; }
        .group-table {
            display: inline-block;
            width: 45%;
            vertical-align: top;
            margin: 0 2%;
        }
        .group-table table {
            width: 100%;
            border-collapse: collapse;
            box-shadow: 0 0 5px rgba(0,0,0,0.1);
        }
        .group-table th {
            background-color: #000;
            color: white;
            padding: 10px;
            text-align: center;
        }
        .group-table td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: center;
        }
        .match-box {
            margin: 15px 0;
            padding: 12px;
            background: #fff;
            border-left: 6px solid #007bff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .match-box img {
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

    <!-- ========== GROUP STAGE TABLES ========== -->
    <h2>Group Stage</h2>
    <div class="group-table">
        <h3>Group A</h3>
        <table>
            <tr>
                <th>Logo</th>
                <th>Team Name</th>
            </tr>
            <?php foreach ($groupA as $team): ?>
                <tr>
                    <td><img src="uploads/<?= htmlspecialchars($team['logo_url'], ENT_QUOTES) ?>" width="40"></td>
                    <td><?= htmlspecialchars($team['team_name'], ENT_QUOTES) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>

        <?php
        // List out each of the 6 matches for Group A in order
        for ($i = 0; $i < count($groupA_matches); $i++) {
            $match = $groupA_matches[$i];
            $t1    = $match['team1'];
            $t2    = $match['team2'];
            $key   = "A" . $i;

            // Date logic: same as schedule.php: "+ $i days"
            $date = new DateTime('2025-08-01');
            $date->modify("+" . $i . " days");
            $dt   = $date->format('l, F j, Y') . " at 17:00";

            echo "<div class='match-box'>\n";
            echo "  <strong>Group A:</strong> \n";
            echo "  <img src='uploads/{$t1['logo_url']}' width='30'> "
                 . htmlspecialchars($t1['team_name'], ENT_QUOTES)
                 . " vs "
                 . "<img src='uploads/{$t2['logo_url']}' width='30'> "
                 . htmlspecialchars($t2['team_name'], ENT_QUOTES)
                 . "<br>\n";
            echo "  <em>Date:</em> {$dt}<br>\n";
            if (isset($stored[$key])) {
                echo "  <strong>Score:</strong> {$stored[$key]['score1']} - {$stored[$key]['score2']}<br>\n";
                echo "  <strong>MOTM:</strong> {$stored[$key]['motm']}<br>\n";
                echo "  <strong>Goalscorers:</strong> {$stored[$key]['goalscorers']}<br>\n";
                echo "  <a href='{$stored[$key]['highlight_url']}' target='_blank'>Watch Highlights</a>\n";
            } else {
                echo "  <em>Not Played Yet</em>\n";
            }
            echo "</div>\n";
        }
        ?>
    </div>

    <div class="group-table">
        <h3>Group B</h3>
        <table>
            <tr>
                <th>Logo</th>
                <th>Team Name</th>
            </tr>
            <?php foreach ($groupB as $team): ?>
                <tr>
                    <td><img src="uploads/<?= htmlspecialchars($team['logo_url'], ENT_QUOTES) ?>" width="40"></td>
                    <td><?= htmlspecialchars($team['team_name'], ENT_QUOTES) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>

        <?php
        // List out each of the 6 matches for Group B
        for ($i = 0; $i < count($groupB_matches); $i++) {
            $match = $groupB_matches[$i];
            $t1    = $match['team1'];
            $t2    = $match['team2'];
            $key   = "B" . $i;

            $date = new DateTime('2025-08-01');
            $date->modify("+" . $i . " days");
            $dt   = $date->format('l, F j, Y') . " at 19:00";

            echo "<div class='match-box'>\n";
            echo "  <strong>Group B:</strong> \n";
            echo "  <img src='uploads/{$t1['logo_url']}' width='30'> "
                 . htmlspecialchars($t1['team_name'], ENT_QUOTES)
                 . " vs "
                 . "<img src='uploads/{$t2['logo_url']}' width='30'> "
                 . htmlspecialchars($t2['team_name'], ENT_QUOTES)
                 . "<br>\n";
            echo "  <em>Date:</em> {$dt}<br>\n";
            if (isset($stored[$key])) {
                echo "  <strong>Score:</strong> {$stored[$key]['score1']} - {$stored[$key]['score2']}<br>\n";
                echo "  <strong>MOTM:</strong> {$stored[$key]['motm']}<br>\n";
                echo "  <strong>Goalscorers:</strong> {$stored[$key]['goalscorers']}<br>\n";
                echo "  <a href='{$stored[$key]['highlight_url']}' target='_blank'>Watch Highlights</a>\n";
            } else {
                echo "  <em>Not Played Yet</em>\n";
            }
            echo "</div>\n";
        }
        ?>
    </div>

    <div style="clear: both;"></div>
    

    <!-- ========== KNOCKOUT STAGE ========== -->

<?php
// Count how many group matches have valid scores
$group_matches_played = 0;
foreach (array_merge($groupA_matches, $groupB_matches) as $i => $match) {
    $key = ($i < 6) ? "A{$i}" : "B" . ($i - 6);
    if (isset($stored[$key]) && is_numeric($stored[$key]['score1']) && is_numeric($stored[$key]['score2'])) {
        $group_matches_played++;
    }
}
?>

<h2>Knockout Stage</h2>
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
    <!-- Semifinal 1 -->
    <?php
    $sf1_key = "SF1";
    $sf1_date = new DateTime('2025-08-07');
    $sf1_dt = $sf1_date->format('l, F j, Y') . " at 17:00";
    echo "<div class='match-box'>\n";
    echo "<strong>Semifinal 1:</strong><br>\n";
    echo "<img src='uploads/{$sf1_team1['logo_url']}' width='30'> " . htmlspecialchars($sf1_team1['team_name']) . " vs ";
    echo "<img src='uploads/{$sf1_team2['logo_url']}' width='30'> " . htmlspecialchars($sf1_team2['team_name']) . "<br>\n";
    echo "<em>Date:</em> {$sf1_dt}<br>\n";
    if (isset($stored[$sf1_key])) {
        echo "<strong>Score:</strong> {$stored[$sf1_key]['score1']} - {$stored[$sf1_key]['score2']}<br>\n";
        echo "<strong>MOTM:</strong> {$stored[$sf1_key]['motm']}<br>\n";
        echo "<strong>Goalscorers:</strong> {$stored[$sf1_key]['goalscorers']}<br>\n";
        echo "<a href='{$stored[$sf1_key]['highlight_url']}' target='_blank'>Watch Highlights</a>\n";
    } else {
        echo "<em>Not Played Yet</em>\n";
    }
    echo "</div>\n";
    ?>

    <!-- Semifinal 2 -->
    <?php
    $sf2_key = "SF2";
    $sf2_date = new DateTime('2025-08-07');
    $sf2_dt = $sf2_date->format('l, F j, Y') . " at 19:00";
    echo "<div class='match-box'>\n";
    echo "<strong>Semifinal 2:</strong><br>\n";
    echo "<img src='uploads/{$sf2_team1['logo_url']}' width='30'> " . htmlspecialchars($sf2_team1['team_name']) . " vs ";
    echo "<img src='uploads/{$sf2_team2['logo_url']}' width='30'> " . htmlspecialchars($sf2_team2['team_name']) . "<br>\n";
    echo "<em>Date:</em> {$sf2_dt}<br>\n";
    if (isset($stored[$sf2_key])) {
        echo "<strong>Score:</strong> {$stored[$sf2_key]['score1']} - {$stored[$sf2_key]['score2']}<br>\n";
        echo "<strong>MOTM:</strong> {$stored[$sf2_key]['motm']}<br>\n";
        echo "<strong>Goalscorers:</strong> {$stored[$sf2_key]['goalscorers']}<br>\n";
        echo "<a href='{$stored[$sf2_key]['highlight_url']}' target='_blank'>Watch Highlights</a>\n";
    } else {
        echo "<em>Not Played Yet</em>\n";
    }
    echo "</div>\n";
    ?>

    <!-- Final -->
    <?php if ($final_team1 && $final_team2): 
        $f_key = "F1";
        $f_date = new DateTime('2025-08-08');
        $f_dt = $f_date->format('l, F j, Y') . " at 18:00";
        echo "<div class='match-box'>\n";
        echo "<strong>Final:</strong><br>\n";
        echo "<img src='uploads/{$final_team1['logo_url']}' width='30'> " . htmlspecialchars($final_team1['team_name']) . " vs ";
        echo "<img src='uploads/{$final_team2['logo_url']}' width='30'> " . htmlspecialchars($final_team2['team_name']) . "<br>\n";
        echo "<em>Date:</em> {$f_dt}<br>\n";
        if (isset($stored[$f_key])) {
            echo "<strong>Score:</strong> {$stored[$f_key]['score1']} - {$stored[$f_key]['score2']}<br>\n";
            echo "<strong>MOTM:</strong> {$stored[$f_key]['motm']}<br>\n";
            echo "<strong>Goalscorers:</strong> {$stored[$f_key]['goalscorers']}<br>\n";
            echo "<a href='{$stored[$f_key]['highlight_url']}' target='_blank'>Watch Highlights</a>\n";
        } else {
            echo "<em>Not Played Yet</em>\n";
        }
        echo "</div>\n";
    endif; ?>
<?php endif; ?>

</body>
</html>
<?php include 'includes/public_footer.php'; ?>
