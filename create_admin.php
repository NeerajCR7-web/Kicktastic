<?php
require 'includes/db.php';

$name = "Admin User";
$email = "myadmin@example.com";
$password = password_hash("admin123", PASSWORD_DEFAULT); 
$role = "league_admin";

$stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $name, $email, $password, $role);

if ($stmt->execute()) {
    echo "✅ Admin user created successfully.<br>Email: admin@example.com<br>Password: admin123";
} else {
    echo "❌ Error: " . $stmt->error;
}
?>
