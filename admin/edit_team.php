<?php
// admin/edit_team.php
session_start();
require '../includes/db.php';

if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'league_admin') {
    header('Location: ../index.php');
    exit;
}

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header('Location: teams.php');
    exit;
}

// Fetch existing data
$stmt = $conn->prepare("SELECT team_name, logo_url FROM teams WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($team_name, $logo_url);
$stmt->fetch();
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_name = $_POST['team_name'];
    $new_logo = $logo_url; // default to existing logo

    // Check if a new file is uploaded
    if (isset($_FILES['logo_file']) && $_FILES['logo_file']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['logo_file']['name'], PATHINFO_EXTENSION);
        $filename = uniqid("logo_", true) . '.' . $ext;
        $destination = "../uploads/" . $filename;

        if (move_uploaded_file($_FILES['logo_file']['tmp_name'], $destination)) {
            $new_logo = $filename;
        }
    }

    // Update database
    $stmt = $conn->prepare("UPDATE teams SET team_name = ?, logo_url = ? WHERE id = ?");
    $stmt->bind_param("ssi", $new_name, $new_logo, $id);
    $stmt->execute();
    $stmt->close();

    header('Location: teams.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Team &middot; KickTastic Admin</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      padding: 2rem;
      background: #f9f9f9;
      display: flex;
      justify-content: center;
    }
    form {
      background: #fff;
      padding: 1.5rem;
      border-radius: 6px;
      max-width: 400px;
      width: 100%;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    label {
      display: block;
      margin-bottom: 0.5rem;
      font-weight: bold;
    }
    input[type="text"],
    input[type="file"] {
      width: 100%;
      padding: 0.5rem;
      margin-bottom: 1rem;
      border: 1px solid #ccc;
      border-radius: 4px;
    }
    input[type="submit"] {
      padding: 0.6rem 1.2rem;
      background: #007bff;
      color: #fff;
      border: none;
      border-radius: 4px;
      cursor: pointer;
    }
    input[type="submit"]:hover {
      background: #0056b3;
    }
    .logo-preview {
      text-align: center;
      margin-bottom: 1rem;
    }
    .logo-preview img {
      width: 80px;
      height: 80px;
      object-fit: cover;
      border-radius: 50%;
      border: 2px solid #ddd;
    }
    a {
      display: inline-block;
      margin-top: 1rem;
      color: #007bff;
    }
  </style>
</head>
<body>
  <form method="POST" enctype="multipart/form-data">
    <h2 style="text-align:center;">Edit Team</h2>

    <label for="team_name">Team Name</label>
    <input type="text" id="team_name" name="team_name" value="<?= htmlspecialchars($team_name) ?>" required>

    <div class="logo-preview">
      <p>Current Logo:</p>
      <img src="../uploads/<?= htmlspecialchars($logo_url) ?>" alt="Current Logo">
    </div>

    <label for="logo_file">Upload New Logo (optional)</label>
    <input type="file" id="logo_file" name="logo_file" accept="image/*">

    <input type="submit" value="Save Changes">
    <a href="teams.php">← Back to Teams</a>
  </form>
</body>
</html>
