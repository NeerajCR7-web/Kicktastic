<?php
session_start();
require '../includes/db.php';

if ($_SESSION['role'] !== 'team_manager') {
    header("Location: ../login.php");
    exit;
}

$player_id = $_GET['id'];
$player = $conn->query("SELECT * FROM players WHERE id = $player_id")->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $position = $_POST['position'];
    $jersey = $_POST['jersey'];
    $image_url = $player['image_url'];

    if (!empty($_FILES['image']['name'])) {
        $upload_dir = "../uploads/";
        $image_name = time() . "_" . basename($_FILES["image"]["name"]);
        $target_file = $upload_dir . $image_name;

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image_url = $image_name;
        }
    }

    $stmt = $conn->prepare("UPDATE players SET name = ?, position = ?, jersey_number = ?, image_url = ? WHERE id = ?");
    $stmt->bind_param("ssisi", $name, $position, $jersey, $image_url, $player_id);
    $stmt->execute();

    header("Location: view_teams.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Player</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f6f9;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            background: #fff;
    padding: 50px;
    border-radius: 12px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
    max-width: 524px;
    width: 100%;
    position: absolute;
    top: 56px;
        }

        .back-link {
            position: absolute;
            top: 15px;
            left: 25px;
            color: #007bff;
            text-decoration: none;
            font-weight: bold;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        h2 {
            font-size: 26px;
            margin-bottom: 20px;
            text-align: center;
        }

        form {
            margin-top: 30px;
        }

        input[type="text"],
        input[type="number"],
        input[type="file"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0 15px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }

        input[type="submit"] {
            background-color: #007bff;
            color: white;
            padding: 10px 18px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            width: 100%;
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
        }

        img {
            border-radius: 8px;
            margin-bottom: 10px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            max-width: 100px;
        }
    </style>
</head>
<body>

<div class="container">
    <a href="view_players.php?team_id=<?= $player['team_id'] ?>" class="back-link">← Back to Player List</a>

    <h2>Edit Player</h2>

    <form method="POST" enctype="multipart/form-data">
        Name: <input type="text" name="name" value="<?= htmlspecialchars($player['name']) ?>" required>
        Position: <input type="text" name="position" value="<?= htmlspecialchars($player['position']) ?>" required>
        Jersey #: <input type="number" name="jersey" value="<?= htmlspecialchars($player['jersey_number']) ?>" required>

        <?php if ($player['image_url']): ?>
            Current Image:<br>
            <img src="../uploads/<?= htmlspecialchars($player['image_url']) ?>"><br>
        <?php endif; ?>

        Change Image: <input type="file" name="image"><br>
        <input type="submit" value="Update Player">
    </form>
</div>

</body>
</html>
