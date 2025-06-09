<?php
session_start();
require '../includes/db.php';

if ($_SESSION['role'] !== 'team_manager') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$team = $conn->query("SELECT * FROM teams WHERE user_id = $user_id")->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $team_name = $_POST['team_name'];
    $college = $_POST['college'];
    $logo_path = $team['logo_url'];

    if (!empty($_FILES['logo']['name'])) {
        $upload_dir = "../uploads/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $logo_name = time() . "_" . basename($_FILES["logo"]["name"]);
        $target_file = $upload_dir . $logo_name;

        if (move_uploaded_file($_FILES["logo"]["tmp_name"], $target_file)) {
            $logo_path = $logo_name;
        }
    }
     $stmt = $conn->prepare("UPDATE teams SET team_name = ?, college = ?, logo_url = ? WHERE user_id = ?");
    $stmt->bind_param("sssi", $team_name, $college, $logo_path, $user_id);
    $stmt->execute();

    header("Location: dashboard.php");
    exit;
}
?>

<h2>Edit Team Info</h2>

<form method="POST" enctype="multipart/form-data">
    Team Name: <input type="text" name="team_name" value="<?= $team['team_name'] ?>" required><br><br>

    College:
    <select name="college" required>
        <option value="">--Select College--</option>
        <?php
        $colleges = [
            "Humber College",
            "Seneca College",
            "George Brown College",
            "Centennial College",
            "Sheridan College",
            "Toronto Metropolitan University"
        ];
         foreach ($colleges as $college) {
            $selected = ($college === $team['college']) ? 'selected' : '';
            echo "<option value=\"$college\" $selected>$college</option>";
        }
        ?>
    </select><br><br>
