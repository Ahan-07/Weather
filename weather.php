<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}
// get ip from network and get current location
function getDefaultCityFromIP() {
  $ip = file_get_contents('https://api.ipify.org');
  $geoUrl = "http://ip-api.com/json/$ip";
  $geoResponse = file_get_contents($geoUrl);
  $geoData = json_decode($geoResponse, true);
  return $geoData && $geoData['status'] === 'success' ? $geoData['city'] : 'Mumbai';
}

$weather = null;
$forecast = [];
$error = null;

$city = isset($_GET['city']) ? urlencode($_GET['city']) : urlencode(getDefaultCityFromIP());
$apiKey = '84e6eee41a71702ca273fca6e1e671ce';
$currentUrl = "https://api.openweathermap.org/data/2.5/weather?q=$city&appid=$apiKey&units=metric";
$forecastUrl = "https://api.openweathermap.org/data/2.5/forecast?q=$city&appid=$apiKey&units=metric";

function fetchAPI($url) {
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  $response = curl_exec($ch);
  curl_close($ch);
  return json_decode($response, true);
}

$currentData = fetchAPI($currentUrl);
$forecastData = fetchAPI($forecastUrl);

if (isset($currentData['cod']) && $currentData['cod'] == 200) {
    $timezoneOffset = $currentData['timezone']; // e.g., 19800 (for IST)

    $dtSunrise = new DateTime('@' . $currentData['sys']['sunrise']);
    $dtSunset  = new DateTime('@' . $currentData['sys']['sunset']);

    // Set proper timezone using offset
    $timezoneName = timezone_name_from_abbr("", $timezoneOffset, 0);
    if ($timezoneName) {
        $dtSunrise->setTimezone(new DateTimeZone($timezoneName));
        $dtSunset->setTimezone(new DateTimeZone($timezoneName));
    } else {
        // fallback to Asia/Kolkata if name is not found 
        $dtSunrise->setTimezone(new DateTimeZone('Asia/Kolkata'));
        $dtSunset->setTimezone(new DateTimeZone('Asia/Kolkata'));
    }

    $weather = [
        'name'        => $currentData['name'],
        'country'     => $currentData['sys']['country'],
        'temp'        => $currentData['main']['temp'],
        'feels_like'  => $currentData['main']['feels_like'],
        'temp_min'    => $currentData['main']['temp_min'],
        'temp_max'    => $currentData['main']['temp_max'],
        'pressure'    => $currentData['main']['pressure'],
        'humidity'    => $currentData['main']['humidity'],
        'visibility'  => $currentData['visibility'],
        'wind'        => $currentData['wind']['speed'],
        'sunrise'     => $dtSunrise->format('h:i A'),
        'sunset'      => $dtSunset->format('h:i A'),
        'timezone'    => ($timezoneOffset / 3600) . ' hrs',
        'desc'        => $currentData['weather'][0]['description'],
        'icon'        => $currentData['weather'][0]['icon'],
        'main'        => $currentData['weather'][0]['main']
    ];


  $days = [];
  foreach ($forecastData['list'] as $entry) {
    if (strpos($entry['dt_txt'], '12:00:00') !== false) {
      $day = date('D, M j', strtotime($entry['dt_txt']));
      if (!in_array($day, $days)) {
        $days[] = $day;
        $forecast[] = [
          'date' => $day,
          'temp' => round($entry['main']['temp']),
          'desc' => $entry['weather'][0]['description'],
          'icon' => $entry['weather'][0]['icon']
        ];
        if (count($forecast) === 5) break;
      }
    }
  }
} else {
  $error = "City not found!";
}

// save visited cities for home page
if ($weather && isset($weather['name'])) {
  $cityName = $weather['name'];
  $icon = $weather['icon'];
  $temp = round($weather['temp']);

  // Insert or update last visited cities
  require 'config.php';
  $stmt = $pdo->prepare("INSERT INTO recent_cities (name, icon, temp, updated_at) VALUES (?, ?, ?, NOW())
                         ON DUPLICATE KEY UPDATE icon = ?, temp = ?, updated_at = NOW()");
  $stmt->execute([$cityName, $icon, $temp, $icon, $temp]);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Advanced Weather App</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: <?= isset($weather) ? match(strtolower($weather['main'])) {
        'clear' => "linear-gradient(to right, #56ccf2, #2f80ed)",
        'clouds' => "linear-gradient(to right, #bdc3c7, #2c3e50)",
        'rain', 'drizzle' => "linear-gradient(to right, #4b79a1, #283e51)",
        'thunderstorm' => "linear-gradient(to right, #141e30, #243b55)",
        'snow' => "linear-gradient(to right, #e0eafc, #cfdef3)",
        'mist', 'fog' => "linear-gradient(to right, #3e5151, #decba4)",
        default => "linear-gradient(to right, #4facfe, #00f2fe)"
      } : '#4facfe'; ?>;
      color: #fff;
    }
    .weather-card {
      background: rgba(0, 0, 0, 0.4);
      border-radius: 20px;
      padding: 30px;
      box-shadow: 0 0 30px rgba(0,0,0,0.3);
    }
    input[type="search"] {
      background-color: rgba(255,255,255,0.2);
      border: none;
      color: white;
    }
    input[type="search"]::placeholder {
      color: #eee;
    }
    .forecast-card {
      background: rgba(255, 255, 255, 0.15);
      border-radius: 12px;
      padding: 15px;
      text-align: center;
      color: #fff;
    }
    .info-card {
      background: rgba(255,255,255,0.2);
      border-radius: 15px;
      padding: 15px;
      margin-bottom: 15px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    }
     .glass-card {
  background: rgba(255, 255, 255, 0.15); /* semi-transparent */
  border-radius: 16px;
  box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.2);
  backdrop-filter: blur(10px);          /* key glass effect */
  -webkit-backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.18);
  transition: 0.3s ease;
}

.glass-card:hover {
  background: rgba(255, 255, 255, 0.25);
  box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
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
  <!-- navbar -->
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
<!-- main  -->
<div class="container py-5">
  <form method="get" class="row justify-content-center mb-4">
    <div class="col-md-6 d-flex">
      <input type="search" name="city" class="form-control form-control-lg me-2" placeholder="Enter city name">
      <button class="btn btn-light btn-lg">Search</button>
    </div>
  </form>

  <?php if ($weather): ?>
    <div class="row justify-content-center">
      <div class="col-md-10 weather-card">
        <div class="text-center">
          <h2><?= $weather['name'] ?>, <?= $weather['country'] ?></h2>
          <img src="https://openweathermap.org/img/wn/<?= $weather['icon'] ?>@2x.png" alt="icon">
          <h1><?= $weather['temp'] ?>°C</h1>
          <p class="lead"><?= ucfirst($weather['desc']) ?></p>
        </div>
        <div class="row text-center mt-4">
          <?php
            $stats = [
              ['Feels Like', $weather['feels_like'] . '°C', 'bg-primary'],
              ['Min Temp', $weather['temp_min'] . '°C', 'bg-success'],
              ['Max Temp', $weather['temp_max'] . '°C', 'bg-danger'],
              ['Pressure', $weather['pressure'] . ' hPa', 'bg-warning text-dark'],
              ['Humidity', $weather['humidity'] . '%', 'bg-info text-dark'],
              ['Visibility', ($weather['visibility']/1000) . ' km', 'bg-light text-dark'],
              ['Wind Speed', $weather['wind'] . ' m/s', 'bg-secondary'],
              ['Timezone', 'UTC ' . $weather['timezone'], 'bg-light']
            ];
            foreach ($stats as [$title, $value, $class]) {
              echo "<div class='col-md-3'><div class='card text-center $class mb-3'><div class='card-body'><h6 class='card-title'>$title</h6><p class='card-text'>$value</p></div></div></div>";
            }
          ?>
        </div>
        <div class="card glass-card d-flex flex-row justify-content-between p-3 ">
  <div class="col-md-6 fw-semibold text-black">Sunrise: <?= $weather['sunrise'] ?></div>
  <div class="col-md-6 fw-semibold text-black">Sunset: <?= $weather['sunset'] ?></div>
        
        </div>
      </div>
    </div>
    <div class="row justify-content-center mt-5">
      <h3 class="text-center mb-4">5-Day Forecast</h3>
      <?php foreach ($forecast as $day): ?>
        <div class="col-md-2 mx-2 forecast-card">
          <div><?= $day['date'] ?></div>
          <img src="https://openweathermap.org/img/wn/<?= $day['icon'] ?>.png" alt="icon">
          <div><?= $day['temp'] ?>°C</div>
          <small><?= ucfirst($day['desc']) ?></small>
        </div>
      <?php endforeach; ?>
    </div>
  <?php elseif ($error): ?>
    <div class="alert alert-danger text-center mt-4"><?= $error ?></div>
  <?php endif; ?>

  
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
