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


