<?php
require 'includes/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $confirm_email = $_POST['confirm_email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $role = 'team_manager';
    $fullname = $fname . " " . $lname;

    if ($email !== $confirm_email) {
        $error = "Emails do not match.";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $fullname, $email, $password, $role);
        if ($stmt->execute()) {
            $success = "Registered successfully. <a href='login.php'>Login here</a>";
        } else {
            $error = "Registration failed. Please try again.";
        }
    }
}
?>