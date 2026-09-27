<?php
require 'config.php';
session_start();

if (!isset($_SESSION['user']) || !isset($_GET['id'])) {
    header("Location: gallery.php");
    exit;
}

$id = $_GET['id'];
$user = $_SESSION['user'];

$stmt = $pdo->prepare("SELECT * FROM gallery WHERE id = ? AND uploaded_by = ?");
$stmt->execute([$id, $user]);
$image = $stmt->fetch();

if (!$image) {
    die("Access denied.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newTitle = $_POST['title'];
    $newCategory = $_POST['category'];

    $pdo->prepare("UPDATE gallery SET title = ?, category = ? WHERE id = ? AND uploaded_by = ?")
        ->execute([$newTitle, $newCategory, $id, $user]);

    header("Location: gallery.php?updated=1");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Image</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .dropdown-menu {
      min-width: 180px;
      max-width: 220px;
      word-break: break-word;
      right: 0;
      left: auto;
    }
    .navbar .dropdown-menu {
      position: absolute;
      right: 0;
      left: auto;
    }
  </style>
</head>
<body class="bg-light">
 <nav class="navbar navbar-expand-lg navbar-dark bg-dark px-4">
  <a class="navbar-brand fw-bold" href="index.php">🌦️ Weather App</a>
  <div class="collapse navbar-collapse">
    <ul class="navbar-nav ms-auto">
      <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
      <li class="nav-item"><a class="nav-link" href="weather.php">Weather</a></li>
      <li class="nav-item"><a class="nav-link" href="gallery.php">Gallery</a></li>
      <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Settings</a>
        <ul class="dropdown-menu">
          <?php if ($user): ?>
            <li class="dropdown-item">👋 Hello, <strong><?= htmlspecialchars($user) ?></strong></li>
            <li class="dropdown-item"><a href="logout.php">Logout</a></li>
          <?php else: ?>
            <li class="dropdown-item"><a href="login.php">Login</a></li>
            <li class="dropdown-item"><a href="register.php">Register</a></li>
          <?php endif; ?>
        </ul>
      </li>
    </ul>
  </div>
</nav>
<div class="container mt-5">
  <h2>Edit Image Details</h2>
  <form method="POST">
    <input name="title" class="form-control mb-3" value="<?= htmlspecialchars($image['title']) ?>" required>
    <select name="category" class="form-select mb-3" required>
      <option <?= $image['category'] == 'Sunny' ? 'selected' : '' ?>>Sunny</option>
      <option <?= $image['category'] == 'Rainy' ? 'selected' : '' ?>>Rainy</option>
      <option <?= $image['category'] == 'Snowy' ? 'selected' : '' ?>>Snowy</option>
      <option <?= $image['category'] == 'Cloudy' ? 'selected' : '' ?>>Cloudy</option>
    </select>
    <button class="btn btn-primary">Update</button>
    <a href="gallery.php" class="btn btn-secondary">Cancel</a>
  </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
