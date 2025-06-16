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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register as Manager</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f4f6f9;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
        }

        .register-box {
            background: #fff;
            padding: 30px 40px;
            border-radius: 16px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            width: 400px;
        }
         .register-box h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #333;
        }

        .register-box label {
            font-weight: 500;
            display: block;
            margin: 12px 0 6px;
            color: #333;
        }

        .register-box input[type="text"],
        .register-box input[type="email"],
        .register-box input[type="password"] {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #ccc;
            border-radius: 8px;
            box-sizing: border-box;
        }
          .register-box input[type="submit"] {
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 20px;
        }
