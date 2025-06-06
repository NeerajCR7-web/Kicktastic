<?php
session_start();
require '../includes/db.php';

$team_name = $_POST['team_name'];
$college = $_POST['college'];
$user_id = $_SESSION['user_id'];
$logo_path = "";