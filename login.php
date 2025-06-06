<?php
session_start();
require 'includes/db.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
   if ($user && password_verify($password, $user['password'])){
    // Check if selected role matches actual user role
    if (isset($_GET['role']) && $_GET['role'] !== $user['role']) {
        die("Access denied for this role.");
    }

       $_SESSION['user_id'] = $user['id'];
    $_SESSION['role'] = $user['role'];

      if ($user['role'] == 'league_admin') {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: manager/dashboard.php");
    }
    exit;