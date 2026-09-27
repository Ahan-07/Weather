<?php
require 'config.php';
$error="";
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = $user['username'];
        header("Location: index.php");
        exit;
    } else {
        $error = "Invalid credentials.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login-weather</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

  <div class="d-flex justify-content-center align-items-center vh-100">
    <div class="card shadow-lg" style="width: 350px;">
      <div class="card-body">
        <h2 class="mb-4 text-center">Login</h2>

        <?php if ($error): ?>
          <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" novalidate>
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required>
          </div>

          <div class="mb-3 position-relative">
            <label for="password" class="form-label">Password</label>
            <div class="input-group">
              <input type="password" class="form-control" id="password" name="password" required>
              <span class="input-group-text password-toggle" id="togglePassword"><i>
                  <img src="eye-fill.svg" alt="" srcset="">
                </i></span>
            </div>

            <button type="submit" name="login" class="btn mt-1 btn-primary w-100">Login</button>
        </form>

        <div class="text-center mt-3">
          Don’t have an account? <a href="register.php">Register</a>
        </div>
      </div>
    </div>
  </div>

  <script>
    const passwordInput = document.getElementById('password');
    const togglePassword = document.getElementById('togglePassword');

    togglePassword.addEventListener('click', () => {
      const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
      passwordInput.setAttribute('type', type);
      togglePassword.innerHTML = type === 'password'
        ? '<i><img src="eye-fill.svg" alt="Show/Hide" /></i>'
        : '<i><img src="eye-slash-fill.svg" alt="Show/Hide" /></i>';
    });
  </script>
</body>
</html>
