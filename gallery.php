<?php
session_start();
require 'config.php';

$user = $_SESSION['user'] ?? null;
if (!$user) {
    header("Location: login.php");
    exit;
}

$filter = $_GET['filter'] ?? '';

// Fetch filtered images
if ($filter) {
    $stmt = $pdo->prepare("SELECT * FROM gallery WHERE category = ?");
    $stmt->execute([$filter]);
} else {
    $stmt = $pdo->query("SELECT * FROM gallery ORDER BY uploaded_at DESC");
}
$images = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Gallery</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(to right, #56ccf2, #2f80ed);
      color: #fff;
    }
    .gallery-img {
      width: 100%;
      height: 220px;
      object-fit: cover;
      transition: 0.4s;
    }
    .gallery-card:hover .gallery-img {
      transform: scale(1.05);
    }
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
<body>

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

<div class="container py-5">
  <h2 class="text-center mb-4">📸 Weather Photo Gallery</h2>

  <!-- Filter -->
  <div class="mb-4 text-center">
    <a href="gallery.php" class="btn btn-light btn-sm <?= $filter == '' ? 'active' : '' ?>">All</a>
    <a href="gallery.php?filter=Sunny" class="btn btn-warning btn-sm <?= $filter == 'Sunny' ? 'active' : '' ?>">Sunny</a>
    <a href="gallery.php?filter=Rainy" class="btn btn-primary btn-sm <?= $filter == 'Rainy' ? 'active' : '' ?>">Rainy</a>
    <a href="gallery.php?filter=Snowy" class="btn btn-info btn-sm <?= $filter == 'Snowy' ? 'active' : '' ?>">Snowy</a>
    <a href="gallery.php?filter=Cloudy" class="btn btn-secondary btn-sm <?= $filter == 'Cloudy' ? 'active' : '' ?>">Cloudy</a>
  </div>

  <!-- Upload Form -->
  <?php if ($user): ?>
  <div class="card p-3 mb-4">
    <form action="upload_img.php" method="POST" enctype="multipart/form-data">
      <div class="row g-2">
        <div class="col-md-4">
          <input name="title" class="form-control" placeholder="Image Title" required>
        </div>
        <div class="col-md-3">
          <select name="category" class="form-select" required>
            <option value="">Choose Category</option>
            <option value="Sunny">Sunny</option>
            <option value="Rainy">Rainy</option>
            <option value="Snowy">Snowy</option>
            <option value="Cloudy">Cloudy</option>
          </select>
        </div>
        <div class="col-md-3">
            <input type="file" name="image" class="form-control" accept="image/*" onchange="previewImage(event)" required>
<img id="preview" src="#" class="mt-2 rounded d-none" width="150">
        </div>
        <div class="col-md-2">
          <button class="btn btn-success w-100">Upload</button>
        </div>
      </div>
    </form>
  </div>
  <?php endif; ?>

  <!-- Image Grid -->
  <div class="row g-4">
  <?php if (empty($images)): ?>
    <div class="col-12 text-center">
      <img src="https://images.pexels.com/photos/1118873/pexels-photo-1118873.jpeg?cs=srgb&dl=pexels-jplenio-1118873.jpg&fm=jpg" class="img-fluid rounded" alt="No images">
      <p class="mt-3">No images uploaded in this category yet. Be the first to upload!</p>
    </div>
  <?php else: ?>
    <?php foreach ($images as $img): ?>
      <div class="col-md-4">
        <div class="card gallery-card bg-white text-dark">
          <img src="<?= htmlspecialchars($img['image_path']) ?>" class="gallery-img" alt="<?= htmlspecialchars($img['title']) ?>">
          <div class="card-body">
            <h5 class="card-title"><?= htmlspecialchars($img['title']) ?></h5>
            <p class="card-text">
              <small>By <?= htmlspecialchars($img['uploaded_by']) ?> |
              <?= date("d M Y", strtotime($img['uploaded_at'])) ?></small>
            </p>
            <?php if ($user && $user === $img['uploaded_by']): ?>
              <div class="d-flex justify-content-between mt-2">
                <a href="edit_image.php?id=<?= $img['id'] ?>" class="btn btn-sm btn-outline-warning">Edit</a>
                <a href="delete_image.php?id=<?= $img['id'] ?>" onclick="return confirm('Delete this image?')" class="btn btn-sm btn-outline-danger">Delete</a>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function previewImage(event) {
  const reader = new FileReader();
  reader.onload = function () {
    const output = document.getElementById('preview');
    output.src = reader.result;
    output.classList.remove('d-none');
  }
  reader.readAsDataURL(event.target.files[0]);
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
