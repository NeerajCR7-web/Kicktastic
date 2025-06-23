<?php
session_start();
require '../includes/db.php';

if ($_SESSION['role'] !== 'team_manager') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$team = $conn->query("SELECT * FROM teams WHERE user_id = $user_id")->fetch_assoc();
$team_id = $team['id'];

// Count players
$count_query = $conn->query("SELECT COUNT(*) as total FROM players WHERE team_id = $team_id");
$player_count = $count_query->fetch_assoc()['total'];

// Handle POST
if ($_SERVER["REQUEST_METHOD"] == "POST" && $player_count < 8) {
    $name = $_POST['name'];
    $position = $_POST['position'];
    $jersey = $_POST['jersey'];
    $image_url = "";

    if (!empty($_FILES['image']['name'])) {
        $upload_dir = "../uploads/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $image_name = time() . "_" . basename($_FILES["image"]["name"]);
        $target_file = $upload_dir . $image_name;

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image_url = $image_name;
        }
    }

    $stmt = $conn->prepare("INSERT INTO players (team_id, name, position, jersey_number, image_url) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issis", $team_id, $name, $position, $jersey, $image_url);
    $stmt->execute();
    header("Location: players.php");
    exit;
}

// fetch players
$players = $conn->query("SELECT * FROM players WHERE team_id = $team_id");
?>

<h2>Manage Players</h2>
<p><strong>Current Players:</strong> <?= $player_count ?> / 8</p>

<?php if ($player_count < 8): ?>
<form method="POST" enctype="multipart/form-data">
    Name: <input type="text" name="name" required><br>
    Position: <input type="text" name="position" required><br>
    Jersey #: <input type="number" name="jersey" required><br>
    Image: <input type="file" name="image"><br><br>
    <input type="submit" value="Add Player">
</form>
<?php else: ?>
    <p style="color: red;">You have reached the maximum limit of 8 players.</p>
<?php endif; ?>

<h3>Current Players:</h3>
<table border="1" cellpadding="10">
<tr><th>Image</th><th>Name</th><th>Position</th><th>Jersey</th><th>Edit</th></tr>
<?php while($p = $players->fetch_assoc()): ?>
<tr>
    <td>
        <?php if ($p['image_url']): ?>
            <img src="../uploads/<?= $p['image_url'] ?>" width="50">
        <?php else: ?>
            No Image
        <?php endif; ?>
    </td>
    <td><?= htmlspecialchars($p['name']) ?></td>
    <td><?= htmlspecialchars($p['position']) ?></td>
    <td>#<?= htmlspecialchars($p['jersey_number']) ?></td>
    <td><a href="edit_player.php?id=<?= $p['id'] ?>">🖉</a></td>
</tr>
<?php endwhile; ?>
</table>

<br>
<a href="dashboard.php">Back to Dashboard</a>
