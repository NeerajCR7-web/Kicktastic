<?php
session_start();
require 'includes/db.php';

$roleLabel = 'User';
if (isset($_GET['role'])) {
    if ($_GET['role'] === 'team_manager') {
        $roleLabel = 'Manager';
    } elseif ($_GET['role'] === 'league_admin') {
        $roleLabel = 'Admin';
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    if ($user && password_verify($password, $user['password'])) {
        if (isset($_GET['role']) && $_GET['role'] !== $user['role']) {
            die("Access denied for this role.");
        }
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_name'] = $user['name'];    // stores the name who logged in
        $_SESSION['role']      = $user['role'];
        if ($user['role'] == 'league_admin') {
            header("Location: admin/index.php");
        } else {
            header("Location: manager/index.php");
        }
        exit;
    } else {
        $error = "Invalid email or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login &middot; KickTastic</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100vh;
      background: #f1f5f9;
      font-family: Arial, sans-serif;
    }
    .login-box {
      position: relative;
      background: #fff;
      width: 360px;
      padding: 2rem;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      text-align: center;
    }
    .login-box .close {
      position: absolute;
      top: 12px;
      right: 12px;
      font-size: 1.5rem;
      color: #555;
      text-decoration: none;
      line-height: 1;
    }
    .login-box .close:hover { color: #000; }
    .brand {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      margin-bottom: 0.5rem;
      text-decoration: none;
    }
    .brand img {
      height: 50px;
      width: 50px;
      object-fit: cover;
      border-radius: 50%;
    }
    .brand .title {
      font-size: 2rem;
      font-weight: bold;
      color: #007bff;
    }
    .role-message {
      margin-bottom: 1.5rem;
      font-size: 1rem;
      color: #555;
    }
    .login-box form {
      display: flex;
      flex-direction: column;
      gap: 1rem;
    }
    .login-box label {
      text-align: left;
      font-size: 0.9rem;
      color: #333;
    }
    .login-box input[type="email"],
    .login-box input[type="password"] {
      padding: 0.75rem;
      font-size: 1rem;
      border: 1px solid #ccc;
      border-radius: 6px;
      width: 100%;
    }
    .login-box input[type="submit"] {
      padding: 0.75rem;
      font-size: 1rem;
      background: #007bff;
      color: #fff;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      transition: background 0.2s;
    }
    .login-box input[type="submit"]:hover {
      background: #0056b3;
    }
    .error {
      margin-bottom: 1rem;
      color: #e53e3e;
      font-size: 0.9rem;
    }
    @media (max-width: 400px) {
      .login-box { width: 90%; padding: 1.5rem; }
    }
  </style>
</head>
<body>

  <div class="login-box">
    <a href="index.php" class="close">&times;</a>
    <a href="index.php" class="brand">
      <img src="assets/images/logo.png" alt="KickTastic Logo">
      <span class="title">KickTastic</span>
    </a>
    <div class="role-message">Sign in as <?= htmlspecialchars($roleLabel) ?></div>

    <?php if (!empty($error)): ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
      <label for="email">Email</label>
      <input type="email" id="email" name="email" required>

      <label for="password">Password</label>
      <input type="password" id="password" name="password" required>

      <input type="submit" value="Login">
    </form>
  </div>

</body>
</html>
