<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= isset($page_title) ? htmlspecialchars($page_title) : 'KickTastic' ?></title>
  <link
    rel="stylesheet"
    href="../assets/css/main.css"
  />
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"
    integrity="sha512-dyZtEEryLpVBj+K6wwd7U8pmPr9/zTOuV/CfiE6sJor0L0F7kk8V1fqTJyVq2mZV9yfeHcC5IuUlG97+MURyZA=="
    crossorigin="anonymous"
    referrerpolicy="no-referrer"
  />
</head>
<body>

  <header class="top-bar">
    <img src="../assets/images/logo.png" alt="KickTastic Logo" class="logo">
    <div class="title-container">
      <div class="site-title">KickTastic</div>
      <div class="slogan">“All Goals. One Platform.”</div>
    </div>

    <?php if (!empty($_SESSION['user_id'])): ?>
      <div class="user-menu">
        <button class="user-button" id="userBtn">
          <?= htmlspecialchars($_SESSION['user_name']) ?> ▼
        </button>
        <div class="user-menu-content" id="userMenu">
          <?php if ($_SESSION['role'] === 'team_manager'): ?>
            <a href="/manager/dashboard.php">My Team</a>
          <?php elseif ($_SESSION['role'] === 'league_admin'): ?>
            <a href="teams.php">Manage Teams</a>
          <?php endif; ?>
          <a href="../logout.php">Log out</a>
        </div>
      </div>
    <?php else: ?>
      <div class="dropdown">
        <button class="dropdown-button" id="loginBtn">Login / Signup</button>
        <div class="dropdown-content" id="dropdownMenu">
          <div class="signin-container">
            <a href="/login.php?role=team_manager" class="signin-box">Manager</a>
            <a href="/login.php?role=league_admin" class="signin-box">Admin</a>
          </div>
          <a href="/register.php" class="register-link">
            Don't have an account?<br>Register as Manager
          </a>
        </div>
      </div>
    <?php endif; ?>
  </header>

  <nav class="menu-bar">
    <a href="../admin/index.php">Home</a>
    <a href="teams.php">Teams</a>
   <a href="../admin/schedule.php">Schedule / Scoresheet</a>
    <a href="standings.php">Standings</a>
       <a href="../admin/managers.php">Users</a>

  </nav>

  <script>
    document.getElementById('loginBtn')?.addEventListener('click', e => {
      e.stopPropagation();
      document.getElementById('dropdownMenu').classList.toggle('show');
    });
    document.getElementById('userBtn')?.addEventListener('click', e => {
      e.stopPropagation();
      document.getElementById('userMenu').classList.toggle('show');
    });
    document.addEventListener('click', () => {
      document.querySelectorAll('.dropdown-content, .user-menu-content')
              .forEach(el => el.classList.remove('show'));
    });
  </script>
