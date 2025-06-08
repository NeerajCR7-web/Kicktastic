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

    header("Location: players.php");
    exit;
}
?>

<h2>Edit Player</h2>

<form method="POST" enctype="multipart/form-data">
    Name: <input type="text" name="name" value="<?= $player['name'] ?>" required><br>
    Position: <input type="text" name="position" value="<?= $player['position'] ?>" required><br>
    Jersey #: <input type="number" name="jersey" value="<?= $player['jersey_number'] ?>" required><br>
    <?php if ($player['image_url']): ?>
        Current Image:<br>
        <img src="../uploads/<?= $player['image_url'] ?>" width="100"><br>
    <?php endif; ?>
    Change Image: <input type="file" name="image"><br><br>
    <input type="submit" value="Update Player">
</form>

<br>
<a href="players.php">Back to Player List</a>
