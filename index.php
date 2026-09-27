<?php
session_start();
require 'config.php'; // Contains $pdo and $apiKey

$user = $_SESSION['user'] ?? null;
$error = '';

// Fetch recent cities
$cities = $pdo->query("SELECT name, icon, temp FROM recent_cities ORDER BY updated_at DESC LIMIT 10")->fetchAll();
$weatherIcon = $cities[0]['icon'] ?? '02d';

function getGradient($icon) {
  return match (substr($icon, 0, 2)) {
    '01' => "linear-gradient(to right, #56ccf2, #2f80ed)",
    '02', '03' => "linear-gradient(to right, #bdc3c7, #2c3e50)",
    '09', '10' => "linear-gradient(to right, #4b79a1, #283e51)",
    '11' => "linear-gradient(to right, #141e30, #243b55)",
    '13' => "linear-gradient(to right, #e0eafc, #cfdef3)",
    '50' => "linear-gradient(to right, #3e5151, #decba4)",
    default => "linear-gradient(to right, #4facfe, #00f2fe)"
  };
}
$bgGradient = getGradient($weatherIcon);

function getQuoteByIcon($icon) {
  return match(substr($icon, 0, 2)) {
    '01' => "☀️ The sun always shines above the clouds!",
    '02', '03' => "☁️ Cloudy minds still hold sunshine.",
    '09', '10' => "🌧️ Don’t forget your umbrella today!",
    '11' => "🌪️ Storms make trees take deeper roots.",
    '13' => "❄️ Cold hands, warm heart.",
    '50' => "🌫️ A little fog never stopped a dreamer.",
    default => "🌤️ Weather is what you get — mood is what you make."
  };
}

$weatherData = null;
if (!empty($_GET['city'])) {
  $city = urlencode(trim($_GET['city']));
  $url = "https://api.openweathermap.org/data/2.5/weather?q=$city&appid=$apiKey&units=metric";
  $response = @file_get_contents($url);
  if ($response === false) {
    $error = "Weather service unavailable.";
  } else {
    $weatherData = json_decode($response, true);
    if ($weatherData['cod'] != 200) {
      $error = "City not found!";
      $weatherData = null;
    } else {
      $weatherIcon = $weatherData['weather'][0]['icon'];
      $bgGradient = getGradient($weatherIcon);
    }
  }
}

$weatherQuote = getQuoteByIcon($weatherIcon);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Weather App Home</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background: <?= $bgGradient ?>; color: #fff; font-family: 'Segoe UI', sans-serif; }
    .city-card { min-width: 180px; background: rgba(255,255,255,0.15); border-radius: 15px; padding: 20px; text-align: center; }
    .scroll-container { overflow-x: auto; display: flex; gap: 20px; padding: 20px; }
    .glass-box { background: rgba(255,255,255,0.1); border-radius: 20px; padding: 30px; backdrop-filter: blur(5px); }
    .welcome { animation: bounce 1s ease-out; font-size: 1.3rem; }
    @keyframes bounce { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
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

<div class="container text-center py-5">
  <h1 class="fw-bold">Welcome to Weather App</h1>
  <p class="fst-italic"><?= $weatherQuote ?></p>
  <?php if ($user): ?><p class="welcome">🌟 Welcome back, <strong><?= htmlspecialchars($user) ?></strong>!</p><?php endif; ?>

  <form action="weather.php" method="get" class="row justify-content-center my-4">
    <div class="col-md-6 d-flex">
      <input type="search" name="city" class="form-control me-2" placeholder="Enter city" required>
      <button class="btn btn-light">Search</button>
    </div>
  </form>

  <div class="d-flex justify-content-center gap-3 flex-wrap">
    <a href="weather.php" class="btn btn-primary">Check Weather</a>
    <a href="gallery.php" class="btn btn-info">View Gallery</a>
  </div>

  <?php if ($cities): ?>
    <h3 class="text-white mt-5">📍 Recently Checked Cities</h3>
    <div class="scroll-container">
      <?php foreach ($cities as $c): ?>
        <a href="weather.php?city=<?= urlencode($c['name']) ?>" class="city-card text-white text-decoration-none">
          <h5><?= htmlspecialchars($c['name']) ?></h5>
          <img src="https://openweathermap.org/img/wn/<?= $c['icon'] ?>.png" alt="">
          <p><?= round($c['temp']) ?>°C</p>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <div class="glass-box mt-4 mx-auto" style="max-width: 700px;">
    <h3>🌐 About This App</h3>
    <p>This app gives real-time weather updates, a beautiful gallery, 5-day forecasts, and a customizable experience!</p>
  </div>

  <?php if ($error): ?><div class="alert alert-danger mt-4"><?= htmlspecialchars($error) ?></div><?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
