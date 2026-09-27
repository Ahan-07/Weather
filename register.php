<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Validate
    if ($username && $email && $password) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        try {
            $stmt->execute([$username, $email, $hashedPassword]);
            header("Location: login.php?registered=1");
            exit;
        } catch (PDOException $e) {
            $error = "Email already registered!";
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
  <title>Register</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .bg-custom{
  background:    linear-gradient(to right, #3e5151, #decba4)
    }
    .glass-box {
      background: rgba(255,255,255,0.1);
      border-radius: 20px;
      padding: 30px;
      margin-top: 30px;
      backdrop-filter: blur(5px);
    }
  </style>
</head>
<body class="bg-custom">
  
   <div class="d-flex justify-content-center align-items-center vh-100">
    <div style="width: 350px;">
      <div class="card glass-box">
        <h2 class="mb-4 text-center">Register</h2>
 <?php if (!empty($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
       
        <form method="POST" novalidate>
          <div class="mb-3">
            <label class="form-label">Username</label>
            <input type="text" name="username" class="form-control" placeholder="Enter Username" required>
          </div>
 <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="eamil" name="email" class="form-control" placeholder="John@Example.com" required>
          </div>
          <div class="mb-3 position-relative">
            <label for="password" class="form-label">Password</label>
            <div class="input-group">
              <input type="password" class="form-control" id="password" name="password" required>
              <span class="input-group-text password-toggle" id="togglePassword"><i>
                  <img src="eye-fill.svg" alt="" srcset="">
                </i></span>
            </div>

            <button type="submit" name="login" class="btn mt-1 btn-success w-100">Register</button>
        </form>

        <div class="text-center mt-3">
          Already have an account?<a href="login.php">Login here</a>
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
