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
