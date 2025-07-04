<?php
session_start();
require '../includes/db.php';
$team_count_result = $conn->query("SELECT COUNT(*) as total FROM teams");
$team_count = $team_count_result->fetch_assoc()['total'];
if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'league_admin') {
    header('Location: /index.php');
    exit;
}

$teamLogos = [];
$teamsResult = $conn->query("SELECT logo_url FROM teams ORDER BY id ASC");
while ($row = $teamsResult->fetch_assoc()) {
    $teamLogos[] = $row['logo_url'];
}
$duplicatedLogos = array_merge($teamLogos, $teamLogos);

$soccerImages = ['../assets/images/1.png',
    '../assets/images/2.jpg',
    '../assets/images/3.jpg',
    
    '../assets/images/8.jpg',
    '../assets/images/9.jpg',
    '../assets/images/10.jpg',
    '../assets/images/11.jpg',
    '../assets/images/12.jpg',
    '../assets/images/13.jpg',
    '../assets/images/14.jpg',
    '../assets/images/15.jpg'];

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
    $groupB_matches = getMatches($groupB); 

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

    $nameToIdA = [];
    foreach ($groupA as $t) {
        $nameToIdA[$t['team_name']] = $t['id'];
    }
    $nameToIdB = [];
    foreach ($groupB as $t) {
        $nameToIdB[$t['team_name']] = $t['id'];
    }

    $res = $conn->query("SELECT match_key, score1, score2 FROM match_results");
    while ($row = $res->fetch_assoc()) {
        $key = $row['match_key'];
        $s1  = intval($row['score1']);
        $s2  = intval($row['score2']);

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
   <head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>KickTastic — Manager Home</title>
  <style>
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

    /* User Dropdown Menu */
    .user-menu, .dropdown { position: relative; flex-shrink: 0; }
.dropdown-button, .user-button {
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
.dropdown-content, .user-menu-content {
  display: none;
  position: absolute;
  right: 0;
  margin-top: 0.5rem;
  background-color: #98caeb;
  min-width: 200px;
  box-shadow: 0 8px 16px rgba(0,0,0,0.2);
  border-radius: 4px;
  z-index: 1000;
  padding: 0.5rem 0;
}
.dropdown-content.show, .user-menu-content.show { display: block; top:37px;}
.dropdown-content a, .user-menu-content a {
  display: block;
  padding: 0.5rem 1rem;
  color: #000;
}
.dropdown-content a:hover, .user-menu-content a:hover {
  background: rgba(0,0,0,0.1);
}

/*Sign in Option*/
.signin-container {
  display: flex;
  gap: 0.5rem;
  padding: 0 1rem 0.5rem;
}
.signin-box {
  flex: 1;
  background: #fff;
  text-align: center;
  padding: 0.5rem;
  border-radius: 30px;
  transition: background 0.2s;
}
.signin-box:hover { background: #e0e0e0; }
.register-link {
  text-align: center;
  padding: 0.5rem 1rem;
  font-size: 0.85rem;
  color: #555;
}
.register-link:hover { color: #3498db; }

    .team-slider-container {
      overflow: hidden;
      background: #fff;
      padding: 1rem 0;
    }
    .team-slider-track {
      display: flex;
      gap: 2rem;
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
    .news-container {
      width: 40%;
      height: 300px;
      overflow-y: auto;
      background: black;
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
      background: #1e88e5; 
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
      background: #000; 
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

    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    body{background:#f9f9f9;font-family:Arial,sans-serif;}
    .top-bar{background:#000;display:flex;align-items:center;justify-content:space-between;height:80px;padding:0 1rem;}
    .logo{height:100%;border-radius:50%;object-fit:cover}
    .title-container{flex-grow:1;display:flex;flex-direction:column;align-items:center}
    .site-title{color:#fff;font-size:2rem;font-weight:bold}
    .slogan{color:#fff;font-size:1rem;margin-top:.25rem}
    .dropdown, .user-menu{position:relative;flex-shrink:0}
    .user-button{padding:.5rem 1rem;background:#3498db;color:#fff;border:none;border-radius:4px;cursor:pointer}
    .user-menu-content{display:none;position:absolute;right:0;margin-top:.5rem;background:#98caeb;padding:12px;border-radius:4px;box-shadow:0 8px 16px rgba(0,0,0,0.2);z-index:1000}
    .user-menu-content.show{display:block; top: -8px;}
    .user-menu-content a{display:block;padding:.5rem 1rem;color:#000;border-radius:4px;text-decoration:none}
    .user-menu-content a:hover{background:rgba(0,0,0,0.1)}
   
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
        font-size: 1rem;
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
 
</head>
</head>
<body>
  <header class="top-bar">
    <img src="../assets/images/logo.png" class="logo" alt="KickTastic">
    <div class="title-container">
      <div class="site-title">KickTastic</div>
      <div class="slogan">“All Goals. One Platform.”</div>
    </div>
    <div class="user-menu">
      <button class="user-button" id="userBtn"><?= htmlspecialchars($_SESSION['user_name']) ?></button>
      <div class="user-menu-content" id="userMenu">
        <a href="teams.php">Manage Teams</a>
        <a href="../logout.php">Log out</a>
      </div>
    </div>
  </header>

<nav class="menu-bar">
    <a href="index.php">Home</a>
    <a href="teams.php">Teams</a>
    <a href="schedule.php">Schedule / Scoresheet</a>
    <a href="../standings.php">Standings</a>
    <a href="managers.php">Users</a>
 
  </nav>
<?php if ($team_count === 8): ?>
    <div style="background-color: #e0f7e9; padding: 20px; border: 2px solid #4caf50; border-radius: 10px; margin-bottom: 20px;">
        <h3>All 8 teams have been registered!</h3>
        <p>You can now proceed to generate the match schedule.</p>
        <form action="schedule.php" method="POST">
            <button type="submit" name="generate_schedule" class="btn btn-primary">Generate Schedule</button>
        </form>
    </div>
<?php else: ?>
    
<?php endif; ?>

<?php if (count($allTeams) === 8): ?>
  <div class="team-slider-container">
    <div class="team-slider-track animate">
      <?php foreach ($duplicatedLogos as $logo): ?>
        <img src="../uploads/<?= htmlspecialchars($logo) ?>" alt="Team Logo">
      <?php endforeach; ?>
    </div>
  </div>
<?php endif; ?>


  <section class="content-section">
    <div class="slider-container" id="sliderContainer">
      <button class="slider-button prev" id="prevBtn">&#8249;</button>
      <img src="<?= htmlspecialchars($soccerImages[0]) ?>" alt="Soccer Image" id="sliderImage">
      <button class="slider-button next" id="nextBtn">&#8250;</button>
    </div>

    <div class="news-container" id="newsContainer">
    </div>
  </section>
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
              <img src="../uploads/<?= htmlspecialchars($matchA['team1']['logo_url']) ?>">
              <div class="team-name"><?= htmlspecialchars($matchA['team1']['team_name']) ?></div>
            </div>
            <span>V/S</span>
            <div class="team">
              <img src="../uploads/<?= htmlspecialchars($matchA['team2']['logo_url']) ?>">
              <div class="team-name"><?= htmlspecialchars($matchA['team2']['team_name']) ?></div>
            </div>
          </div>
        </div>
        <?php $matchB = $groupB_matches[$i]; $dateB = (new DateTime('2025-08-01'))->modify("+{$i} days")->format('F j, Y'); ?>
        <div class="schedule-box">
          <div class="match-date">Group B - <?= $dateB ?></div>
          <div class="teams-row">
            <div class="team">
              <img src="../uploads/<?= htmlspecialchars($matchB['team1']['logo_url']) ?>">
              <div class="team-name"><?= htmlspecialchars($matchB['team1']['team_name']) ?></div>
            </div>
            <span>V/S</span>
            <div class="team">
              <img src="../uploads/<?= htmlspecialchars($matchB['team2']['logo_url']) ?>">
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
            <td><img src="../uploads/<?= htmlspecialchars($row['logo']) ?>"></td>
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
            <td><img src="../uploads/<?= htmlspecialchars($row['logo']) ?>"></td>
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

  

 <footer class="site-footer">
  <div class="footer-follow">
    <span class="follow-text">Follow Us On</span>
    <div class="social-icons">
      <a href="https://www.instagram.com/YourPage" target="_blank" class="social-link">
        <img src="../assets/images/instat.png" alt="Instagram" style="height: 44px;" />
      </a>
      <a href="https://www.snapchat.com/add/YourPage" target="_blank" class="social-link">
        <img src="../assets/images/snapc.png" alt="Snapchat" style="height: 61px;" />
      </a>
      <a href="https://www.youtube.com/YourChannel" target="_blank" class="social-link">
        <img src="../assets/images/yout.png" alt="YouTube" style="height: 61px;" />
      </a>
      <a href="https://www.facebook.com/YourPage" target="_blank" class="social-link">
        <img src="../assets/images/faceb.png" alt="Facebook" style="height: 54px;" />
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
    document.getElementById('userBtn').addEventListener('click', e => {
      e.stopPropagation();
      document.getElementById('userMenu').classList.toggle('show');
    });
    document.addEventListener('click', () => {
      document.getElementById('userMenu').classList.remove('show');
    });
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
            img.src = article.urlToImage || '../assets/images/placeholder.jpg';
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
