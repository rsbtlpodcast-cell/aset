<?php
require_once __DIR__ . '/../inc/auth.php';

// Jika sudah login langsung ke index
if (isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role = $_POST['role'] ?? 'user';

    if (login($username, $password, $role)) {
        header("Location: index.php");
        exit;
    } else {
        $error = "Username, password, atau role salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Login - SIAT RSUD Bedas Tegalluar</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(135deg, #4facfe, #00f2fe);
      font-family: 'Segoe UI', sans-serif;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
    }
    .login-card {
      width: 400px;
      padding: 30px;
      border-radius: 20px;
      background: #fff;
      box-shadow: 0 8px 25px rgba(0,0,0,0.2);
      animation: fadeIn 1s ease-in-out;
    }
    .login-card img {
      width: 200px; /* ukuran logo diperbesar */
      margin-bottom: 20px;
    }
    .login-card h4 {
      margin-bottom: 25px;
      font-weight: bold;
      color: #333;
    }
    .btn-custom {
      border-radius: 30px;
      font-weight: 500;
    }
    @keyframes fadeIn {
      from {opacity: 0; transform: translateY(-20px);}
      to {opacity: 1; transform: translateY(0);}
    }
  </style>
</head>
<body>
  <div class="login-card text-center">
    <img src="../img/logorsud.png" alt="Logo RSUD">
    <h4>SIAT RSUD Bedas Tegalluar</h4>

    <?php if ($error): ?>
      <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="mb-3 text-start">
        <label class="form-label">Username</label>
        <input type="text" name="username" class="form-control" required>
      </div>
      <div class="mb-3 text-start">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" required>
      </div>
      <div class="mb-3 text-start">
        <label class="form-label">Login Sebagai</label>
        <select name="role" class="form-select">
          <option value="user">User</option>
          <option value="admin">Admin</option>
        </select>
      </div>
      <button type="submit" class="btn btn-primary w-100 btn-custom mb-2">Login</button>
      <a href="register.php" class="btn btn-outline-secondary w-100 btn-custom">Registrasi</a>
    </form>
  </div>
</body>
</html>
