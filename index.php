<?php
// Include database connection to fetch registered teams and prepare schedule
require 'includes/db.php';

// ----- Fetch registered teams (for logo slider) -----
$teamLogos = [];
$teamsResult = $conn->query("SELECT id, team_name, logo_url FROM teams ORDER BY id ASC");
$allTeamsById = [];
while ($row = $teamsResult->fetch_assoc()) {
    $teamLogos[] = $row['logo_url'];
    $allTeamsById[$row['id']] = [
        'team_name' => $row['team_name'],
        'logo_url'  => $row['logo_url']
    ];
}
// Duplicate logos for seamless scrolling
$duplicatedLogos = array_merge($teamLogos, $teamLogos);

// ----- Define soccer-related images for the left slider -----
$soccerImages = [
    'assets/images/1.png',
    'assets/images/2.jpg',
    'assets/images/3.jpg',
    
    'assets/images/8.jpg',
    'assets/images/9.jpg',
    'assets/images/10.jpg',
    'assets/images/11.jpg',
    'assets/images/12.jpg',
    'assets/images/13.jpg',
    'assets/images/14.jpg',
    'assets/images/15.jpg'

];

// ----- Build group-stage matches for schedule preview -----
$allTeams = [];
$allTeamsResult = $conn->query("SELECT id, team_name, logo_url FROM teams ORDER BY id ASC");
while ($row = $allTeamsResult->fetch_assoc()) {
    $allTeams[] = $row;
}

// Ensure exactly 8 teams
if (count($allTeams) === 8) {
    shuffle($allTeams);
    $groupA = array_slice($allTeams, 0, 4);
    $groupB = array_slice($allTeams, 4, 4);

    function getMatches($group) {
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

    $groupA_matches = getMatches($groupA); // 6 matches
    $groupB_matches = getMatches($groupB); // 6 matches

    // ----- Compute Standings for Group A & Group B -----
    $statsA = [];
    foreach ($groupA as $team) {
        $statsA[$team['id']] = [
            'team_id'   => $team['id'],
            'team_name' => $team['team_name'],
            'logo'      => $team['logo_url'],
            'points'    => 0
        ];
    }
    $statsB = [];
    foreach ($groupB as $team) {
        $statsB[$team['id']] = [
            'team_id'   => $team['id'],
            'team_name' => $team['team_name'],
            'logo'      => $team['logo_url'],
            'points'    => 0
        ];
    }

    // Build name→ID lookup for group teams
    $nameToIdA = [];
    foreach ($groupA as $t) {
        $nameToIdA[$t['team_name']] = $t['id'];
    }
    $nameToIdB = [];
    foreach ($groupB as $t) {
        $nameToIdB[$t['team_name']] = $t['id'];
    }

    // Fetch all saved match results
    $res = $conn->query("SELECT match_key, score1, score2 FROM match_results");
    while ($row = $res->fetch_assoc()) {
        $key = $row['match_key'];
        $s1  = intval($row['score1']);
        $s2  = intval($row['score2']);

        // Group A matches: key starts with "A"
        if (strpos($key, 'A') === 0) {
            $idx = intval(substr($key, 1));
            if (isset($groupA_matches[$idx])) {
                $t1   = $groupA_matches[$idx]['team1']['team_name'];
                $t2   = $groupA_matches[$idx]['team2']['team_name'];
                $id1  = $nameToIdA[$t1];
                $id2  = $nameToIdA[$t2];
                if ($s1 > $s2) {
                    $statsA[$id1]['points'] += 3;
                } elseif ($s1 < $s2) {
                    $statsA[$id2]['points'] += 3;
                } else {
                    $statsA[$id1]['points'] += 1;
                    $statsA[$id2]['points'] += 1;
                }
            }
        }
        // Group B matches: key starts with "B"
        elseif (strpos($key, 'B') === 0) {
            $idx = intval(substr($key, 1));
            if (isset($groupB_matches[$idx])) {
                $t1   = $groupB_matches[$idx]['team1']['team_name'];
                $t2   = $groupB_matches[$idx]['team2']['team_name'];
                $id1  = $nameToIdB[$t1];
                $id2  = $nameToIdB[$t2];
                if ($s1 > $s2) {
                    $statsB[$id1]['points'] += 3;
                } elseif ($s1 < $s2) {
                    $statsB[$id2]['points'] += 3;
                } else {
                    $statsB[$id1]['points'] += 1;
                    $statsB[$id2]['points'] += 1;
                }
            }
        }
    }

    // Sort each group’s standings by points descending
    usort($statsA, function($a, $b) {
        return $b['points'] - $a['points'];
    });
    usort($statsB, function($a, $b) {
        return $b['points'] - $a['points'];
    });
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
  />
  <title>KickTastic</title>
  <style>
    /* ===== 1. RESET ===== */
    *, *::before, *::after {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }
    html {
      font-family: Arial, sans-serif;
      line-height: 1.5;
      color: #111;
    }
    body {
      background: #f9f9f9;
    }
    a {
      text-decoration: none;
      color: inherit;
    }

    /* ===== 2. TOP BAR ===== */
    .top-bar {
      background-color: #000;
      display: flex;
      align-items: center;
      justify-content: space-between;
      height: 80px;
      padding: 0 1rem;
    }
    .top-bar .logo {
      height: 100%;
      width: auto;
      border-radius: 50%;
      object-fit: cover;
      flex-shrink: 0;
    }
    .top-bar .title-container {
      flex-grow: 1;
      display: flex;
      flex-direction: column;
      align-items: center;
    }
    .top-bar .site-title {
      color: #fff;
      font-size: 3rem;
      font-weight: bold;
    }
    .top-bar .slogan {
      color: #fff;
      font-size: 1rem;
      margin-top: -0.75rem;
    }

    /* ===== 3. NAV MENU ===== */
    .menu-bar {
      background-color: #3498db;
      display: flex;
      justify-content: center;
      padding: 0.5rem 0;
    }
    .menu-bar a {
      color: #fff;
      font-size: 1rem;
      margin: 0 1rem;
      padding: 0.25rem 3.5rem;
      transition: background-color 0.2s;
    }
    .menu-bar a:hover {
      background-color: rgba(255,255,255,0.2);
      border-radius: 4px;
    }

    /* ===== 4. DROPDOWN ===== */
    .dropdown {
      position: relative;
      flex-shrink: 0;
    }
    .dropdown-button {
      padding: 0.5rem 1rem;
      font-size: 1rem;
      background-color: #3498db;
      color: #fff;
      border: none;
      cursor: pointer;
      border-radius: 4px;
      height: 40px;
      display: flex;
      align-items: center;
    }
    .dropdown-content {
      display: none;
      position: absolute;
      right: 0;
      margin-top: 0.5rem;
      background-color: #98caeb;
      min-width: 260px;
      box-shadow: 0 8px 16px rgba(0,0,0,0.2);
      border-radius: 4px;
      z-index: 1000;
      padding: 12px;
    }
    .dropdown-content.show {
      display: block;
    }
    .signin-container {
      display: flex;
      gap: 8px;
    }
    .signin-box {
      flex: 1;
      background-color: #f9f9f9;
      padding: 10px 12px;
      border-radius: 30px;
      text-align: center;
      color: #000;
      font-size: 0.875rem;
      transition: background-color 0.2s;
    }
    .signin-box:hover {
      background-color: #e0e0e0;
    }
    .register-link {
      display: block;
      margin-top: 12px;
      text-align: center;
      color: #555;
      font-size: 0.8125rem;
      transition: color 0.2s;
    }
    .register-link:hover {
      color: #3498db;
    }

    /* ===== 5. TEAM LOGO SLIDER ===== */
    .team-slider-container {
      overflow: hidden;
      background: #fff;
      padding: 1rem 0;
    }
   .team-slider-track {
  display: flex;
  gap: 2rem;
}

.team-slider-track.animate {
  animation: scroll-left 20s linear infinite;
}


    
    .team-slider-track img {
      width: 60px;
      height: 60px;
      border-radius: 50%;
      object-fit: cover;
      border: 2px solid #ddd;
    }
    @keyframes scroll-left {
      0% { transform: translateX(0); }
      100% { transform: translateX(-50%); }
    }
    @media (min-width: 640px) {
      .team-slider-track img {
        width: 80px;
        height: 80px;
      }
    }

    /* ===== 6. IMAGE SLIDER & CONTAINERS ===== */
    .content-section {
      display: flex;
      justify-content: space-between;
      gap: 2rem;
      padding: 2rem 1rem;
      background: #fefefe;
      flex-wrap: wrap;
    }
    .slider-container {
      position: relative;
      width: 55%;
      height: 300px;
      background: #000;
      overflow: hidden;
      border-radius: 8px;
    }
    .slider-container img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
    .slider-button {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      background-color: rgba(0, 0, 0, 0.5);
      border: none;
      color: #fff;
      width: 32px;
      height: 32px;
      border-radius: 50%;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .slider-button.prev {
      left: 8px;
    }
    .slider-button.next {
      right: 8px;
    }

    /* ===== 7. NEWS CONTAINER ===== */
    .news-container {
      width: 40%;
      height: 300px;
      overflow-y: auto;
      background: #fff;
      border-radius: 8px;
      padding: 0.5rem;
      box-shadow: 0 1px 4px rgba(0,0,0,0.1);
    }
    .news-item {
      display: flex;
      background: #f9f9f9;
      border-radius: 6px;
      margin-bottom: 0.5rem;
      overflow: hidden;
      height: 90px;
    }
    .news-item img {
      width: 70px;
      height: 100%;
      object-fit: cover;
      flex-shrink: 0;
    }
    .news-text {
      padding: 0.5rem;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }
    .news-text .title {
      font-size: 0.8125rem;
      font-weight: bold;
      color: #333;
      margin-bottom: 0.25rem;
    }
    .news-text .description {
      font-size: 0.6875rem;
      color: #555;
      flex-grow: 1;
      overflow: hidden;
    }
    .news-text .link {
      font-size: 0.6875rem;
      color: #3498db;
      margin-top: 0.25rem;
    }

    /* ===== 8. SCHEDULE PREVIEW ===== */
    .schedule-section {
      padding: 2rem 1rem;
      background: #000;
      border-radius: 20px;
      margin: 2rem 1rem;
    }
    .schedule-heading {
      font-size: 1.5rem;
      font-weight: bold;
      margin-bottom: 1rem;
      text-align: center;
      color: #fff;
    }
    .schedule-container {
      display: flex;
      flex-wrap: wrap;
      gap: 1rem;
      justify-content: center;
    }
    .schedule-box {
      background: #fff;
      border-radius: 30px;
      padding: 1rem;
      width: 220px;
      text-align: center;
      box-shadow: 0 2px 6px rgba(0,0,0,0.15);
    }
    .schedule-box .teams-row {
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 0.5rem;
      margin-bottom: 0.5rem;
    }
    .schedule-box .team {
      display: flex;
      flex-direction: column;
      align-items: center;
    }
    .schedule-box .team img {
      width: 50px;
      height: 50px;
      border-radius: 50%;
      object-fit: cover;
      margin-bottom: 0.25rem;
    }
    .schedule-box .team-name {
      font-size: 1rem;
      font-weight: bold;
      color: #333;
      white-space: nowrap;
    }
    .schedule-box .match-date {
      font-size: 0.875rem;
      color: #333;
      margin-top: 0.5rem;
    }
    .view-full-link {
      display: block;
      margin: 1.5rem auto 0;
      text-align: center;
      font-size: 1rem;
      color: #3498db;
      font-weight: bold;
      width: fit-content;
    }
    .view-full-link:hover {
      text-decoration: underline;
    }

    /* ===== 9. STANDINGS PREVIEW WITH COLOR SCHEME ===== */
    .standings-section {
      background: #000;
      border-radius: 20px;
      margin: 2rem 1rem;
      padding: 2rem 1rem;
    }
    .standings-heading {
      font-size: 1.5rem;
      font-weight: bold;
      margin-bottom: 1rem;
      text-align: center;
      color: #fff;
    }
    .group-standings {
      display: flex;
      justify-content: center;
      gap: 2rem;
      flex-wrap: wrap;
      margin-bottom: 1rem;
    }
    .group-box {
      width: 240px;
      background: #fff;
      border-radius: 15px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
      overflow: hidden;
    }
    .group-box .group-title {
      background: #1e88e5; /* Blue header */
      color: #fff;
      padding: 0.75rem;
      font-size: 1.125rem;
      font-weight: bold;
      text-align: center;
    }
    .standings-table {
      width: 100%;
      border-collapse: collapse;
    }
    .standings-table thead tr {
      background: #000; /* Black header row */
    }
    .standings-table thead th {
      color: #fff;
      padding: 0.5rem;
      font-size: 0.875rem;
      text-align: left;
    }
    .standings-table tbody tr {
      background: #fff;
    }
    .standings-table tbody td {
      padding: 0.5rem;
      font-size: 0.875rem;
      border-bottom: 1px solid #eee;
      vertical-align: middle;
    }
    .standings-table tbody td img {
      width: 24px;
      height: 24px;
      border-radius: 50%;
      object-fit: cover;
      margin-right: 0.5rem;
      vertical-align: middle;
    }
    .view-standings-link {
      display: block;
      margin: 1rem auto 0;
      text-align: center;
      font-size: 1rem;
      color: #3498db;
      font-weight: bold;
      width: fit-content;
    }
    .view-standings-link:hover {
      text-decoration: underline;
    }

    /* ===== 10. FOOTER ===== */
    .site-footer {
      background-color: #000;
      color: #fff;
      padding: 2rem 1rem;
      text-align: center;
      font-size: 0.9rem;
      line-height: 1.6;
    }
    .footer-follow {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      margin-bottom: 1.5rem;
      gap: 1rem;
    }
    .follow-text {
      font-size: 1.25rem;
      margin-right: 0.5rem;
    }
    .social-icons {
      display: flex;
      gap: 1rem;
    }
    .social-link {
      color: #fff;
      font-size: 1.5rem;
      width: 40px;
      height: 40px;
      display: flex;
      align-items: center;
      justify-content: center;
      border: 2px solid #fff;
      border-radius: 50%;
      transition: background-color 0.2s, color 0.2s;
    }
    
    .footer-legal {
      max-width: 950px;
      margin: 0 auto;
    }
    .copyright {
      font-weight: bold;
      margin-bottom: 0.75rem;
      font-size: xx-small;
    }
    .trademark {
      font-size: 0.8rem;
      color: #ccc;
    }

    /* ===== 11. PAGE CONTENT ===== */
    .page-content {
      padding: 2rem 1rem;
      text-align: center;
    }
    .user-menu {
      display: flex;
      align-items: center;
      gap: 1rem;
    }
    .welcome {
      color: #fff;
      font-weight: bold;
    }
    .logout {
      color: #fff;
      background: #e53e3e;
      padding: 0.5rem 1rem;
      border-radius: 4px;
      text-decoration: none;
      transition: background 0.2s;
    }
    .logout:hover {
      background: #c53030;
    }

    /* ===== 12. RESPONSIVE TWEAKS ===== */
    @media (min-width: 768px) {
      .slider-container {
        width: 60%;
        height: 350px;
      }
      .news-container {
        width: 35%;
        height: 350px;
      }
      .news-item {
        height: 100px;
      }
      .news-item img {
        width: 80px;
      }
      .schedule-box {
        width: 260px;
      }
      .schedule-box .team img {
        width: 60px;
        height: 60px;
      }
      .schedule-box .team-name {
        font-size: 1.125rem;
      }
      .schedule-box .match-date {
        font-size: 1rem;
      }
      .standings-table tbody td,
      .standings-table thead th {
        font-size: 1rem;
        padding: 0.75rem;
      }
      .group-box {
        width: 300px;
      }
      .group-box .group-title {
        font-size: 1.25rem;
      }
      .team-slider-track img {
        width: 80px;
        height: 80px;
      }
      .follow-text {
        font-size: 1.5rem;
      }
      .social-link {
        font-size: 1.75rem;
        width: 48px;
        height: 48px;
      }
      .trademark {
        font-size: 0.85rem;
      }
    }
  </style>

  <!-- ===== FONT AWESOME CDN ===== -->
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"
    integrity="sha512-dyZtEEryLpVBj+K6wwd7U8pmPr9/zTOuV/CfiE6sJor0L0F7kk8V1fqTJyVq2mZV9yfeHcC5IuUlG97+MURyZA=="
    crossorigin="anonymous"
    referrerpolicy="no-referrer"
  />
</head>
<body>

  <!-- ===== TOP BAR ===== -->
  <header class="top-bar">
    <img src="assets/images/logo.png" alt="KickTastic Logo" class="logo">

    <div class="title-container">
      <div class="site-title">KickTastic</div>
      <div class="slogan">“All Goals. One Platform.”</div>
    </div>

    <div class="dropdown">
         <?php if (!empty($_SESSION['user_id'])): ?>
        <div class="user-menu">
          <span class="welcome">Hello, <?= htmlspecialchars($_SESSION['user_name']) ?></span>
          <a href="logout.php" class="logout">Log out</a>
        </div>
      <?php else: ?>
      <button class="dropdown-button" id="loginBtn">Login / Signup</button>
      <?php endif; ?>
      <div class="dropdown-content" id="dropdownMenu">
        <div class="signin-container">
          <a href="login.php?role=team_manager" class="signin-box">Sign in as Manager</a>
          <a href="login.php?role=league_admin" class="signin-box">Sign in as Admin</a>
        </div>
        <hr style="margin: 12px 0; border: none; border-top: 1px solid #eee;">
        <a href="register.php" class="register-link">
          Don't have an account?<br>
          Register as Manager
        </a>
      </div>
    </div>
  </header>

  <!-- ===== NAV MENU ===== -->
  <nav class="menu-bar">
    <a href="index.php">Home</a>
    <a href="teams.php">Teams</a>
    <a href="public_schedule.php">Schedule / Scoresheet</a>
    <a href="standings.php">Standings</a>
  </nav>

  <!-- ===== TEAM LOGO SLIDER ===== -->
  <!-- ===== TEAM LOGO SLIDER ===== -->
<?php if (count($allTeams) === 8): ?>
  <div class="team-slider-container">
    <div class="team-slider-track animate">
      <?php foreach ($duplicatedLogos as $logo): ?>
        <img src="uploads/<?= htmlspecialchars($logo) ?>" alt="Team Logo">
      <?php endforeach; ?>
    </div>
  </div>
<?php endif; ?>



  <!-- ===== IMAGE SLIDER & NEWS SECTION ===== -->
  <section class="content-section">
    <!-- Left: Image slider with prev/next buttons -->
    <div class="slider-container" id="sliderContainer">
      <button class="slider-button prev" id="prevBtn">&#8249;</button>
      <img src="<?= htmlspecialchars($soccerImages[0]) ?>" alt="Soccer Image" id="sliderImage">
      <button class="slider-button next" id="nextBtn">&#8250;</button>
    </div>

    <div class="news-container" id="newsContainer">
    </div>
  </section>

  <!-- ===== SCHEDULE PREVIEW ===== -->
  <section class="schedule-section">
  <div class="schedule-heading">Upcoming Matches</div>
  <div class="schedule-container">
    <?php if (count($allTeams) === 8): ?>
      <?php for ($i = 0; $i < 2; $i++): ?>
        <?php $matchA = $groupA_matches[$i]; $dateA = (new DateTime('2025-08-01'))->modify("+{$i} days")->format('F j, Y'); ?>
        <div class="schedule-box">
          <div class="match-date">Group A - <?= $dateA ?></div>
          <div class="teams-row">
            <div class="team">
              <img src="uploads/<?= htmlspecialchars($matchA['team1']['logo_url']) ?>">
              <div class="team-name"><?= htmlspecialchars($matchA['team1']['team_name']) ?></div>
            </div>
            <span>V/S</span>
            <div class="team">
              <img src="uploads/<?= htmlspecialchars($matchA['team2']['logo_url']) ?>">
              <div class="team-name"><?= htmlspecialchars($matchA['team2']['team_name']) ?></div>
            </div>
          </div>
        </div>
        <?php $matchB = $groupB_matches[$i]; $dateB = (new DateTime('2025-08-01'))->modify("+{$i} days")->format('F j, Y'); ?>
        <div class="schedule-box">
          <div class="match-date">Group B - <?= $dateB ?></div>
          <div class="teams-row">
            <div class="team">
              <img src="uploads/<?= htmlspecialchars($matchB['team1']['logo_url']) ?>">
              <div class="team-name"><?= htmlspecialchars($matchB['team1']['team_name']) ?></div>
            </div>
            <span>V/S</span>
            <div class="team">
              <img src="uploads/<?= htmlspecialchars($matchB['team2']['logo_url']) ?>">
              <div class="team-name"><?= htmlspecialchars($matchB['team2']['team_name']) ?></div>
            </div>
          </div>
        </div>
      <?php endfor; ?>
      <a href="public_schedule.php" class="view-full-link">View Full Schedule</a>
    <?php else: ?>
      <p style="text-align:center; color:#fff; font-size:1.1rem">Schedule will appear once all 8 teams have registered.</p>
    <?php endif; ?>
  </div>
</section>

  <!-- ===== STANDINGS PREVIEW ===== -->
 <section class="standings-section">
  <div class="standings-heading">Live Group Standings</div>
  <?php if (!empty($statsA) && !empty($statsB)): ?>
  <div class="group-standings">
    <div class="group-box">
      <div class="group-title">Group A</div>
      <table class="standings-table">
        <thead>
          <tr><th>Logo</th><th>Team</th><th>Pts</th></tr>
        </thead>
        <tbody>
          <?php foreach ($statsA as $row): ?>
          <tr>
            <td><img src="uploads/<?= htmlspecialchars($row['logo']) ?>"></td>
            <td><?= htmlspecialchars($row['team_name']) ?></td>
            <td><?= $row['points'] ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div class="group-box">
      <div class="group-title">Group B</div>
      <table class="standings-table">
        <thead>
          <tr><th>Logo</th><th>Team</th><th>Pts</th></tr>
        </thead>
        <tbody>
          <?php foreach ($statsB as $row): ?>
          <tr>
            <td><img src="uploads/<?= htmlspecialchars($row['logo']) ?>"></td>
            <td><?= htmlspecialchars($row['team_name']) ?></td>
            <td><?= $row['points'] ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <a href="standings.php" class="view-standings-link">View Full Standings</a>
  <?php else: ?>
    <p style="text-align:center; color:#fff; font-size:1.1rem">Standings will be visible after matches begin.</p>
  <?php endif; ?>
</section>

  

  <!-- ===== FOOTER ===== -->
  <footer class="site-footer">
  <div class="footer-follow">
    <span class="follow-text">Follow Us On</span>
    <div class="social-icons">
      <a href="https://www.instagram.com/YourPage" target="_blank" class="social-link">
        <img src="assets/images/instat.png" alt="Instagram" style="height: 44px;" />
      </a>
      <a href="https://www.snapchat.com/add/YourPage" target="_blank" class="social-link">
        <img src="assets/images/snapc.png" alt="Snapchat" style="height: 61px;" />
      </a>
      <a href="https://www.youtube.com/YourChannel" target="_blank" class="social-link">
        <img src="assets/images/yout.png" alt="YouTube" style="height: 61px;" />
      </a>
      <a href="https://www.facebook.com/YourPage" target="_blank" class="social-link">
        <img src="assets/images/faceb.png" alt="Facebook" style="height: 54px;" />
      </a>
    </div>
  </div>


    <div class="footer-legal">
      <p class="copyright">© 2025 KickTastic. All rights reserved.</p>
      <p class="trademark">
        The KickTastic word, the KickTastic logo and all marks related to KickTastic competitions,
        are protected by trademarks and/or copyright of KickTastic. No use for commercial
        purposes may be made of such trademarks. Use of KickTastic signifies your agreement
        to the Terms and Conditions and Privacy Policy.
      </p>
    </div>
  </footer>

  <script>
    // Toggle dropdown on button click
    document.getElementById('loginBtn').addEventListener('click', function(event) {
      event.stopPropagation();
      document.getElementById('dropdownMenu').classList.toggle('show');
    });
    document.addEventListener('click', function() {
      var menu = document.getElementById('dropdownMenu');
      if (menu.classList.contains('show')) {
        menu.classList.remove('show');
      }
    });

    // Image slider functionality
    (function() {
      var images = <?php echo json_encode($soccerImages); ?>;
      var currentIndex = 0;
      var sliderImage = document.getElementById('sliderImage');
      var prevBtn = document.getElementById('prevBtn');
      var nextBtn = document.getElementById('nextBtn');

      function updateImage() {
        sliderImage.src = images[currentIndex];
      }

      prevBtn.addEventListener('click', function() {
        currentIndex = (currentIndex - 1 + images.length) % images.length;
        updateImage();
      });

      nextBtn.addEventListener('click', function() {
        currentIndex = (currentIndex + 1) % images.length;
        updateImage();
      });
    })();

    // Fetch and display news from NewsAPI.org
    (function() {
      var apiKey = '3a24bbdfb9a546f2bac0a19d0671104c';
      var query = encodeURIComponent('UEFA OR "nations league" OR "la liga" OR "league 1" OR bundesliga OR "FIFA world cup"');
      var url = 'https://newsapi.org/v2/everything?q=' + query + '&language=en&sortBy=publishedAt&pageSize=10&apiKey=' + apiKey;

      fetch(url)
        .then(response => response.json())
        .then(data => {
          var container = document.getElementById('newsContainer');
          data.articles.forEach(article => {
            var item = document.createElement('div');
            item.className = 'news-item';

            var img = document.createElement('img');
            img.src = article.urlToImage || 'assets/images/placeholder.jpg';
            img.alt = 'News Image';

            var textDiv = document.createElement('div');
            textDiv.className = 'news-text';

            var title = document.createElement('div');
            title.className = 'title';
            title.textContent = article.title;

            var desc = document.createElement('div');
            desc.className = 'description';
            desc.textContent = article.description || '';

            var link = document.createElement('a');
            link.className = 'link';
            link.href = article.url;
            link.target = '_blank';
            link.textContent = 'Read more';

            textDiv.appendChild(title);
            textDiv.appendChild(desc);
            textDiv.appendChild(link);

            item.appendChild(img);
            item.appendChild(textDiv);

            container.appendChild(item);
          });
        })
        .catch(err => console.error('Error fetching news:', err));
    })();
  </script>

</body>
</html>
