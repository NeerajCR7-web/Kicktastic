<?php
session_start();
require '../includes/db.php';
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['../generate_schedule'])) {
}

$teams_result = $conn->query("SELECT id, team_name, logo_url FROM teams ORDER BY id ASC");
$all_teams = [];
while ($row = $teams_result->fetch_assoc()) {
    $all_teams[] = $row;
}
$team_count = count($all_teams);

if ($team_count !== 8) {

    include '../includes/header.php';
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
    include '../includes/footer.php';
    exit;
}


$teams_by_id = [];
foreach ($all_teams as $t) {
    $teams_by_id[$t['id']] = $t;
}

$results = [];
$res = $conn->query("SELECT * FROM match_results");
while ($r = $res->fetch_assoc()) {
    $results[$r['match_key']] = $r;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['match_key'])) {
    $stmt = $conn->prepare("
        REPLACE INTO match_results 
          (match_key, score1, score2, motm, highlight_url, goalscorers) 
        VALUES 
          (?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param(
        "siisss",
        $_POST['match_key'],
        $_POST['score1'],
        $_POST['score2'],
        $_POST['motm'],
        $_POST['highlight_url'],
        $_POST['goalscorers']
    );
    $stmt->execute();
    $stmt->close();

    $results = [];
    $res2 = $conn->query("SELECT * FROM match_results");
    while ($r2 = $res2->fetch_assoc()) {
        $results[$r2['match_key']] = $r2;
    }
}

function get_matches(array $group) {
    $pairs = [];
    for ($i = 0; $i < count($group); $i++) {
        for ($j = $i + 1; $j < count($group); $j++) {
            $pairs[] = [$group[$i], $group[$j]];
        }
    }
    return $pairs; // 6 matches per 4-team group
}

$groupA = array_slice($all_teams, 0, 4);
$groupB = array_slice($all_teams, 4, 4);

$groupA_matches = get_matches($groupA); 
$groupB_matches = get_matches($groupB); 

$start_date  = new DateTime('2025-08-01');
$match_times = ["17:00", "19:00"]; 


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


for ($i = 0; $i < count($groupA_matches); $i++) {
    $match = $groupA_matches[$i];
    $t1    = $match[0];
    $t2    = $match[1];
    $key   = "A" . $i;
    if (!isset($results[$key])) {
        continue;
    }
    $r  = $results[$key];
    $s1 = intval($r['score1']);
    $s2 = intval($r['score2']);

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


for ($i = 0; $i < count($groupB_matches); $i++) {
    $match = $groupB_matches[$i];
    $t1    = $match[0];
    $t2    = $match[1];
    $key   = "B" . $i;
    if (!isset($results[$key])) {
        continue;
    }
    $r  = $results[$key];
    $s1 = intval($r['score1']);
    $s2 = intval($r['score2']);

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


$groupA_winner   = $statsA[0]['id'];
$groupA_runnerup = $statsA[1]['id'];
$groupB_winner   = $statsB[0]['id'];
$groupB_runnerup = $statsB[1]['id'];

$sf1_team1 = $teams_by_id[$groupA_winner];
$sf1_team2 = $teams_by_id[$groupB_runnerup];
$sf2_team1 = $teams_by_id[$groupB_winner];
$sf2_team2 = $teams_by_id[$groupA_runnerup];

$sf1_result = isset($results['SF1']) ? $results['SF1'] : null;
$sf2_result = isset($results['SF2']) ? $results['SF2'] : null;

$final_team1 = null;
$final_team2 = null;
if ($sf1_result && $sf2_result) {
    if (intval($sf1_result['score1']) > intval($sf1_result['score2'])) {
        $final_team1 = $sf1_team1;
    } else {
        $final_team1 = $sf1_team2;
    }
    if (intval($sf2_result['score1']) > intval($sf2_result['score2'])) {
        $final_team2 = $sf2_team1;
    } else {
        $final_team2 = $sf2_team2;
    }
}
$final_result = ($final_team1 && $final_team2 && isset($results['F1'])) 
                  ? $results['F1'] 
                  : null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_schedule'])) {
    require '../generate_schedule.php';
}

?>
<?php include '../includes/header.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Schedule (Admin)</title>
    <style>
        body { font-family: Arial, sans-serif;  background: #f5f5f5; }
        h2 { margin-top: 40px; color: #333; }
        .match-box {
            margin: 15px 0;
            padding: 12px;
            background: #fff;
            border-left: 6px solid #007bff;
            cursor: pointer;
            box-shadow: 0 1px 4px rgba(0,0,0,0.1);
        }
        .match-box img {
            vertical-align: middle;
            margin-right: 6px;
            width: 28px;
            height: 28px;
            object-fit: cover;
            border-radius: 50%;
        }
        .modal {
            display: none;
            position: fixed;
            top: 20%;
            left: 50%;
            transform: translate(-50%, -20%);
            background: #fff;
            border: 2px solid #007bff;
            padding: 20px;
            z-index: 1000;
            width: 360px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            border-radius: 8px;
        }
        .modal input, .modal textarea {
            display: block;
            margin-bottom: 12px;
            padding: 8px;
            width: 100%;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .modal label {
            font-weight: bold;
            margin-bottom: 4px;
            display: block;
            color: #333;
        }
        .modal button {
            margin-right: 10px;
            padding: 8px 14px;
            border: none;
            border-radius: 4px;
            background: #007bff;
            color: #fff;
            cursor: pointer;
        }
        .modal button:hover {
            background: #0056b3;
        }
        .overlay {
            display: none;
            position: fixed;
            background: rgba(0,0,0,0.5);
            top: 0; left: 0; right: 0; bottom: 0;
            z-index: 999;
        }
    </style>
    <script>
        function openModal(key) {
      
            document.getElementById('score1').value = '';
            document.getElementById('score2').value = '';
            document.getElementById('motm').value = '';
            document.getElementById('highlight_url').value = '';
            document.getElementById('goalscorers').value = '';

            
            var box = document.getElementById(key);
            if (box) {
                var existingScore1    = box.getAttribute('data-score1');
                var existingScore2    = box.getAttribute('data-score2');
                var existingMotm      = box.getAttribute('data-motm');
                var existingHighlight = box.getAttribute('data-highlight');
                var existingScorers   = box.getAttribute('data-scorers');

                if (existingScore1 !== '')    document.getElementById('score1').value       = existingScore1;
                if (existingScore2 !== '')    document.getElementById('score2').value       = existingScore2;
                if (existingMotm  !== '')     document.getElementById('motm').value         = existingMotm;
                if (existingHighlight !== '') document.getElementById('highlight_url').value = existingHighlight;
                if (existingScorers !== '')   document.getElementById('goalscorers').value   = existingScorers;
            }

            document.getElementById('match_key').value = key;
            document.getElementById('modal').style.display   = 'block';
            document.getElementById('overlay').style.display = 'block';
        }
        function closeModal() {
            document.getElementById('modal').style.display   = 'none';
            document.getElementById('overlay').style.display = 'none';
        }
    </script>
</head>
<body>


 
    <h2>Group A Matches</h2>
    <?php
    for ($i = 0; $i < count($groupA_matches); $i++) {
        $match = $groupA_matches[$i];
        $t1    = $match[0];
        $t2    = $match[1];
        $key   = "A" . $i;
        $date  = clone $start_date;
        $date->modify("+" . $i . " days");
        $dt    = $date->format('F j, Y') . " at " . $match_times[0];

        $ds1   = isset($results[$key]) ? intval($results[$key]['score1']) : '';
        $ds2   = isset($results[$key]) ? intval($results[$key]['score2']) : '';
        $dm    = isset($results[$key]) ? htmlspecialchars($results[$key]['motm'], ENT_QUOTES) : '';
        $dh    = isset($results[$key]) ? htmlspecialchars($results[$key]['highlight_url'], ENT_QUOTES) : '';
        $sc    = isset($results[$key]) ? htmlspecialchars($results[$key]['goalscorers'], ENT_QUOTES) : '';

        echo "<div 
                class='match-box' 
                id='{$key}'
                data-score1='{$ds1}' 
                data-score2='{$ds2}' 
                data-motm='{$dm}' 
                data-highlight='{$dh}'
                data-scorers='{$sc}'
                onclick=\"openModal('{$key}')\"
             >
            <strong>Group A:</strong> 
            <img src='../uploads/{$t1['logo_url']}' alt='logo'> " 
            . htmlspecialchars($t1['team_name'], ENT_QUOTES) . " 
            vs 
            <img src='../uploads/{$t2['logo_url']}' alt='logo'> " 
            . htmlspecialchars($t2['team_name'], ENT_QUOTES) . "<br>
            <em>Date:</em> {$dt}<br>";

        if (isset($results[$key])) {
            echo "<strong>Score:</strong> {$results[$key]['score1']} - {$results[$key]['score2']}<br>
                  <strong>MOTM:</strong> {$results[$key]['motm']}<br>
                  <strong>Goalscorers:</strong> {$results[$key]['goalscorers']}<br>
                  <a href='{$results[$key]['highlight_url']}' target='_blank'>Watch Highlights</a>";
        } else {
            echo "<em>Not Played Yet</em>";
        }
        echo "</div>";
    }
    ?>

    <h2>Group B Matches</h2>
    <?php
    for ($i = 0; $i < count($groupB_matches); $i++) {
        $match = $groupB_matches[$i];
        $t1    = $match[0];
        $t2    = $match[1];
        $key   = "B" . $i;
        $date  = clone $start_date;
        $date->modify("+" . $i . " days");
        $dt    = $date->format('F j, Y') . " at " . $match_times[1];

        $ds1 = isset($results[$key]) ? intval($results[$key]['score1']) : '';
        $ds2 = isset($results[$key]) ? intval($results[$key]['score2']) : '';
        $dm  = isset($results[$key]) ? htmlspecialchars($results[$key]['motm'], ENT_QUOTES) : '';
        $dh  = isset($results[$key]) ? htmlspecialchars($results[$key]['highlight_url'], ENT_QUOTES) : '';
        $sc  = isset($results[$key]) ? htmlspecialchars($results[$key]['goalscorers'], ENT_QUOTES) : '';

        echo "<div 
                class='match-box' 
                id='{$key}'
                data-score1='{$ds1}' 
                data-score2='{$ds2}' 
                data-motm='{$dm}' 
                data-highlight='{$dh}'
                data-scorers='{$sc}'
                onclick=\"openModal('{$key}')\"
             >
            <strong>Group B:</strong> 
            <img src='../uploads/{$t1['logo_url']}' alt='logo'> " 
            . htmlspecialchars($t1['team_name'], ENT_QUOTES) . " 
            vs 
            <img src='../uploads/{$t2['logo_url']}' alt='logo'> " 
            . htmlspecialchars($t2['team_name'], ENT_QUOTES) . "<br>
            <em>Date:</em> {$dt}<br>";

        if (isset($results[$key])) {
            echo "<strong>Score:</strong> {$results[$key]['score1']} - {$results[$key]['score2']}<br>
                  <strong>MOTM:</strong> {$results[$key]['motm']}<br>
                  <strong>Goalscorers:</strong> {$results[$key]['goalscorers']}<br>
                  <a href='{$results[$key]['highlight_url']}' target='_blank'>Watch Highlights</a>";
        } else {
            echo "<em>Not Played Yet</em>";
        }
        echo "</div>";
    }
    ?>

    <h2>Knockout Stage</h2>

    <?php
    $sf1_key = "SF1";
    $sf1_date = new DateTime('2025-08-07');
    $sf1_dt   = $sf1_date->format('F j, Y') . " at 17:00";

    $sf1_ds1 = isset($results[$sf1_key]) ? intval($results[$sf1_key]['score1']) : '';
    $sf1_ds2 = isset($results[$sf1_key]) ? intval($results[$sf1_key]['score2']) : '';
    $sf1_dm  = isset($results[$sf1_key]) ? htmlspecialchars($results[$sf1_key]['motm'], ENT_QUOTES) : '';
    $sf1_dh  = isset($results[$sf1_key]) ? htmlspecialchars($results[$sf1_key]['highlight_url'], ENT_QUOTES) : '';
    $sf1_sc  = isset($results[$sf1_key]) ? htmlspecialchars($results[$sf1_key]['goalscorers'], ENT_QUOTES) : '';

    echo "<div 
            class='match-box' 
            id='{$sf1_key}'
            data-score1='{$sf1_ds1}' 
            data-score2='{$sf1_ds2}' 
            data-motm='{$sf1_dm}' 
            data-highlight='{$sf1_dh}'
            data-scorers='{$sf1_sc}'
            onclick=\"openModal('{$sf1_key}')\"
         >
        <strong>Semifinal 1:</strong><br>
        <img src='../uploads/{$sf1_team1['logo_url']}' alt='logo'> " 
        . htmlspecialchars($sf1_team1['team_name'], ENT_QUOTES) . " 
        vs 
        <img src='../uploads/{$sf1_team2['logo_url']}' alt='logo'> " 
        . htmlspecialchars($sf1_team2['team_name'], ENT_QUOTES) . "<br>
        <em>Date:</em> {$sf1_dt}<br>";

    if (isset($results[$sf1_key])) {
        echo "<strong>Score:</strong> {$results[$sf1_key]['score1']} - {$results[$sf1_key]['score2']}<br>
              <strong>MOTM:</strong> {$results[$sf1_key]['motm']}<br>
              <strong>Goalscorers:</strong> {$results[$sf1_key]['goalscorers']}<br>
              <a href='{$results[$sf1_key]['highlight_url']}' target='_blank'>Watch Highlights</a>";
    } else {
        echo "<em>Not Played Yet</em>";
    }
    echo "</div>";
    ?>

    <?php
    $sf2_key = "SF2";
    $sf2_date = new DateTime('2025-08-07');
    $sf2_dt   = $sf2_date->format('F j, Y') . " at 19:00";

    $sf2_ds1 = isset($results[$sf2_key]) ? intval($results[$sf2_key]['score1']) : '';
    $sf2_ds2 = isset($results[$sf2_key]) ? intval($results[$sf2_key]['score2']) : '';
    $sf2_dm  = isset($results[$sf2_key]) ? htmlspecialchars($results[$sf2_key]['motm'], ENT_QUOTES) : '';
    $sf2_dh  = isset($results[$sf2_key]) ? htmlspecialchars($results[$sf2_key]['highlight_url'], ENT_QUOTES) : '';
    $sf2_sc  = isset($results[$sf2_key]) ? htmlspecialchars($results[$sf2_key]['goalscorers'], ENT_QUOTES) : '';

    echo "<div 
            class='match-box' 
            id='{$sf2_key}'
            data-score1='{$sf2_ds1}' 
            data-score2='{$sf2_ds2}' 
            data-motm='{$sf2_dm}' 
            data-highlight='{$sf2_dh}'
            data-scorers='{$sf2_sc}'
            onclick=\"openModal('{$sf2_key}')\"
         >
        <strong>Semifinal 2:</strong><br>
        <img src='../uploads/{$sf2_team1['logo_url']}' alt='logo'> " 
        . htmlspecialchars($sf2_team1['team_name'], ENT_QUOTES) . " 
        vs 
        <img src='../uploads/{$sf2_team2['logo_url']}' alt='logo'> " 
        . htmlspecialchars($sf2_team2['team_name'], ENT_QUOTES) . "<br>
        <em>Date:</em> {$sf2_dt}<br>";

    if (isset($results[$sf2_key])) {
        echo "<strong>Score:</strong> {$results[$sf2_key]['score1']} - {$results[$sf2_key]['score2']}<br>
              <strong>MOTM:</strong> {$results[$sf2_key]['motm']}<br>
              <strong>Goalscorers:</strong> {$results[$sf2_key]['goalscorers']}<br>
              <a href='{$results[$sf2_key]['highlight_url']}' target='_blank'>Watch Highlights</a>";
    } else {
        echo "<em>Not Played Yet</em>";
    }
    echo "</div>";
    ?>

    <?php if ($final_team1 && $final_team2): 
        $f_key   = "F1";
        $f_date  = new DateTime('2025-08-08');
        $f_dt    = $f_date->format('F j, Y') . " at 18:00";

        $f_ds1 = isset($results[$f_key]) ? intval($results[$f_key]['score1']) : '';
        $f_ds2 = isset($results[$f_key]) ? intval($results[$f_key]['score2']) : '';
        $f_dm  = isset($results[$f_key]) ? htmlspecialchars($results[$f_key]['motm'], ENT_QUOTES) : '';
        $f_dh  = isset($results[$f_key]) ? htmlspecialchars($results[$f_key]['highlight_url'], ENT_QUOTES) : '';
        $f_sc  = isset($results[$f_key]) ? htmlspecialchars($results[$f_key]['goalscorers'], ENT_QUOTES) : '';

        echo "<div 
                class='match-box' 
                id='{$f_key}'
                data-score1='{$f_ds1}' 
                data-score2='{$f_ds2}' 
                data-motm='{$f_dm}' 
                data-highlight='{$f_dh}'
                data-scorers='{$f_sc}'
                onclick=\"openModal('{$f_key}')\"
             >
            <strong>Final:</strong><br>
            <img src='../uploads/{$final_team1['logo_url']}' alt='logo'> " 
            . htmlspecialchars($final_team1['team_name'], ENT_QUOTES) . " 
            vs 
            <img src='../uploads/{$final_team2['logo_url']}' alt='logo'> " 
            . htmlspecialchars($final_team2['team_name'], ENT_QUOTES) . "<br>
            <em>Date:</em> {$f_dt}<br>";

        if (isset($results[$f_key])) {
            echo "<strong>Score:</strong> {$results[$f_key]['score1']} - {$results[$f_key]['score2']}<br>
                  <strong>MOTM:</strong> {$results[$f_key]['motm']}<br>
                  <strong>Goalscorers:</strong> {$results[$f_key]['goalscorers']}<br>
                  <a href='{$results[$f_key]['highlight_url']}' target='_blank'>Watch Highlights</a>";
        } else {
            echo "<em>Not Played Yet</em>";
        }
        echo "</div>";
    endif; ?>

    <div id="overlay" class="overlay" onclick="closeModal()"></div>
    <div id="modal" class="modal">
        <form method="post" action="schedule.php">
            <input type="hidden" name="match_key" id="match_key">

            <label for="score1">Score Team 1:</label>
            <input type="number" name="score1" id="score1" required>

            <label for="score2">Score Team 2:</label>
            <input type="number" name="score2" id="score2" required>

            <label for="motm">Man of the Match:</label>
            <input type="text" name="motm" id="motm" required>

            <label for="goalscorers">Goalscorers:</label>
            <textarea name="goalscorers" id="goalscorers" rows="3" placeholder="Name (minute), Name (minute)"></textarea>

            <label for="highlight_url">Video Highlight URL:</label>
            <input type="url" name="highlight_url" id="highlight_url" required>

            <button type="submit">Save</button>
            <button type="button" onclick="closeModal()">Cancel</button>
        </form>
    </div>

</body>
</html>
<?php include '../includes/footer.php'; ?>