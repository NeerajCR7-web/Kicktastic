<?php
// match_logic.php

function getAllTeams(PDO $pdo) {
    $stmt = $pdo->query("SELECT id, team_name, logo_url FROM teams ORDER BY id ASC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getRoundRobinMatches(array $fourTeams) {
    $pairs = [];
    for ($i = 0; $i < 4; $i++) {
        for ($j = $i + 1; $j < 4; $j++) {
            $pairs[] = [
                'team1' => $fourTeams[$i],
                'team2' => $fourTeams[$j]
            ];
        }
    }
    return $pairs;
}
function getAllResults(PDO $pdo) {
    $stmt = $pdo->query("SELECT * FROM match_results");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $out  = [];
    foreach ($rows as $r) {
        $out[ $r['match_key'] ] = $r;
    }
    return $out;
}

function computeGroupStandings(PDO $pdo) {
    $allTeams = getAllTeams($pdo);
    if (count($allTeams) !== 8) {
        return [[], []]; // Return empty if not 8 teams
    }

    
    $groupA = array_slice($allTeams, 0, 4);
    $groupB = array_slice($allTeams, 4, 4);
    $matchesA = getRoundRobinMatches($groupA);
    $matchesB = getRoundRobinMatches($groupB);
    $allResults = getAllResults($pdo);

     $statsA = [];
    foreach ($groupA as $t) {
        $statsA[$t['id']] = [
            'id' => $t['id'],
            'name' => $t['team_name'],
            'played' => 0, 'won' => 0, 'draw' => 0, 'lost' => 0,
            'gf' => 0, 'ga' => 0, 'points' => 0, 'form' => []
        ];
    }

$statsB = [];
    foreach ($groupB as $t) {
        $statsB[$t['id']] = [
            'id' => $t['id'],
            'name' => $t['team_name'],
            'played' => 0, 'won' => 0, 'draw' => 0, 'lost' => 0,
            'gf' => 0, 'ga' => 0, 'points' => 0, 'form' => []
        ];
    }

      // Tally A & B - code continues same (truncated for brevity)...
    // [Keep your tally logic unchanged here]

    return [$statsA, $statsB];
}
function getSemifinalists(PDO $pdo) {
    list($statsA, $statsB) = computeGroupStandings($pdo);
    if (count($statsA) < 2 || count($statsB) < 2) return [];

    $sf1 = ['team1' => $statsA[0]['id'], 'team2' => $statsB[1]['id']];
    $sf2 = ['team1' => $statsB[0]['id'], 'team2' => $statsA[1]['id']];
    return [$sf1, $sf2];
}
function getFinalists(PDO $pdo) {
    $allResults = getAllResults($pdo);
    if (!isset($allResults['SF1']) || !isset($allResults['SF2'])) {
        return null;
    }

    list($sf1Match, $sf2Match) = getSemifinalists($pdo);
    $sf1_t1 = $sf1Match['team1'];
    $sf1_t2 = $sf1Match['team2'];
    $sf2_t1 = $sf2Match['team1'];
    $sf2_t2 = $sf2Match['team2'];

    $winner1 = (intval($allResults['SF1']['score1']) > intval($allResults['SF1']['score2'])) ? $sf1_t1 : $sf1_t2;
    $winner2 = (intval($allResults['SF2']['score1']) > intval($allResults['SF2']['score2'])) ? $sf2_t1 : $sf2_t2;
    return [$winner1, $winner2];
}
function renderMatchBox(string $matchKey, array $team1, array $team2, string $dateTime, array $allResults, bool $isEditable) {
    $ds1 = isset($allResults[$matchKey]) ? intval($allResults[$matchKey]['score1']) : '';
    $ds2 = isset($allResults[$matchKey]) ? intval($allResults[$matchKey]['score2']) : '';
    $dm  = isset($allResults[$matchKey]) ? htmlspecialchars($allResults[$matchKey]['motm'], ENT_QUOTES) : '';
    $dh  = isset($allResults[$matchKey]) ? htmlspecialchars($allResults[$matchKey]['highlight_url'], ENT_QUOTES) : '';

    $attrs = "";
    if ($isEditable) {
        $attrs .= " id='{$matchKey}'"
                . " data-score1='{$ds1}'"
                . " data-score2='{$ds2}'"
                . " data-motm='{$dm}'"
                . " data-highlight='{$dh}'"
                . " onclick=\"openModal('{$matchKey}')\"";
    }

    echo "<div class='match-box'{$attrs}>\n";
    echo "  <strong>{$matchKey}:</strong> \n";
    echo "  <img src='uploads/{$team1['logo_url']}' width='30' alt='logo'> "
         . htmlspecialchars($team1['team_name'], ENT_QUOTES)
         . " vs "
         . "<img src='uploads/{$team2['logo_url']}' width='30' alt='logo'> "
         . htmlspecialchars($team2['team_name'], ENT_QUOTES)
         . "<br>\n";
    echo "  <em>Date:</em> {$dateTime}<br>\n";
    if (isset($allResults[$matchKey])) {
        echo "  <strong>Score:</strong> {$allResults[$matchKey]['score1']} - {$allResults[$matchKey]['score2']}<br>\n";
        echo "  <strong>MOTM:</strong> {$allResults[$matchKey]['motm']}<br>\n";
        echo "  <a href='{$allResults[$matchKey]['highlight_url']}' target='_blank'>Watch Highlights</a>\n";