<?php
session_start();
require 'config.php'; // DB connection

$user = $_SESSION['user'] ?? null;
if (!$user) {
    header("Location: login.php");
    exit;
}

$name = $email = $subject = $message = "";
$success = $error = "";

// Generate captcha if not set
if (!isset($_SESSION['captcha'])) {
  $_SESSION['captcha'] = rand(1000, 9999);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $name = trim($_POST['name']);
  $email = trim($_POST['email']);
  $subject = trim($_POST['subject']);
  $message = trim($_POST['message']);
  $userCaptcha = $_POST['captcha'] ?? '';

  if ($name && $email && $subject && $message) {
    if ($userCaptcha == $_SESSION['captcha']) {
      // Insert into DB
      $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
      $stmt->execute([$name, $email, $subject, $message]);

      $success = "Thanks, $name! Your message has been submitted.";
      $name = $email = $subject = $message = ""; // Clear form
      $_SESSION['captcha'] = rand(1000, 9999); // Refresh captcha
    } else {
      $error = "Invalid CAPTCHA code.";
    }
  } else {
    $error = "Please fill in all fields.";
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Contact Us</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(to right, #667eea, #764ba2);
      color: #fff;
    }
    .contact-container {
      max-width: 700px;
      background: rgba(255,255,255,0.1);
      backdrop-filter: blur(10px);
      border-radius: 20px;
      padding: 40px;
      margin-top: 60px;
    }
    .form-control {
      border-radius: 10px;
    }
    .btn-send {
      background: #fff;
      color: #764ba2;
      font-weight: bold;
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
<div class="container d-flex justify-content-center">
  <div class="contact-container">
    <h2 class="text-center mb-4">📬 Contact Us</h2>

    <?php if ($success): ?>
      <div class="alert alert-success"><?= $success ?></div>
    <?php elseif ($error): ?>
      <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="mb-3">
        <label class="form-label">Name</label>
        <input name="name" value="<?= htmlspecialchars($name) ?>" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input name="email" value="<?= htmlspecialchars($email) ?>" type="email" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Subject</label>
        <input name="subject" value="<?= htmlspecialchars($subject) ?>" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Message</label>
        <textarea name="message" class="form-control" rows="4" required><?= htmlspecialchars($message) ?></textarea>
      </div>

      <!-- CAPTCHA -->
      <div class="mb-3 row">
        <div class="col-md-4">
          <label class="form-label">CAPTCHA</label>
          <input type="text" class="form-control bg-dark text-white text-center fw-bold" value="<?= $_SESSION['captcha'] ?>" readonly>
        </div>
        <div class="col-md-8">
          <label class="form-label">Enter CAPTCHA</label>
          <input name="captcha" class="form-control" required>
        </div>
      </div>

      <button type="submit" class="btn btn-send w-100">Send Message</button>
    </form>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
