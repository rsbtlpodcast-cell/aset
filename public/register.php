<?php
require_once __DIR__ . '/../inc/db.php'; // Pastikan file db.php berisi $conn = new mysqli(...);
require_once __DIR__ . '/../inc/auth.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password_plain = $_POST['password'];
    $role = $_POST['role'];

    // Validasi input
    if (strlen($username) < 3) {
        $error = "Username minimal 3 karakter.";
    } elseif (strlen($password_plain) < 6) {
        $error = "Password minimal 6 karakter.";
    } else {
        $password = password_hash($password_plain, PASSWORD_DEFAULT);

        // Cek apakah username sudah ada
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Username sudah terdaftar!";
        } else {
            // Pastikan role valid
            $allowed_roles = ['admin', 'user'];
            if (!in_array($role, $allowed_roles)) {
                $role = 'user';
            }

            $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $password, $role);

            if ($stmt->execute()) {
                header("Location: login.php?registered=1");
                exit;
            } else {
                $error = "Gagal mendaftar, silakan coba lagi.";
            }
        }
    }
}
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Registrasi - SIAT</title>
<style>
body {
  margin: 0;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  background: linear-gradient(135deg, #4facfe, #00f2fe);
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
}
.auth-card {
  background: #fff;
  padding: 30px;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  width: 380px;
  text-align: center;
  animation: fadeIn 0.6s ease-in-out;
}
.auth-card img {
  width: 150px;
  height: auto;
  margin-bottom: 20px;
}
.auth-card h2 { margin-bottom: 20px; color: #333; }
.auth-card input, .auth-card select {
  width: 100%;
  padding: 12px;
  margin: 8px 0;
  border: 1px solid #ccc;
  border-radius: 8px;
  box-sizing: border-box;
}
.auth-card button {
  width: 100%;
  padding: 12px;
  background: #4facfe;
  border: none;
  border-radius: 8px;
  color: #fff;
  font-size: 16px;
  cursor: pointer;
  transition: background 0.3s;
}
.auth-card button:hover { background: #00c6ff; }
.auth-card p { margin-top: 15px; font-size: 14px; }
.auth-card a { color: #4facfe; text-decoration: none; font-weight: bold; }
.auth-card a:hover { text-decoration: underline; }
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-10px); }
  to   { opacity: 1; transform: translateY(0); }
}
</style>
</head>
<body>
<div class="auth-card">
  <img src="../img/logorsud.png" alt="Logo RSUD">
  <h2>Registrasi Akun</h2>

  <?php if($error): ?>
    <div style="color:red; margin-bottom:10px;"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="post">
    <input type="text" name="username" placeholder="Username" required>
    <input type="password" name="password" placeholder="Password" required>
    <select name="role" required>
      <option value="user">User</option>
      <option value="admin">Admin</option>
    </select>
    <button type="submit">Daftar</button>
  </form>
  <p>Sudah punya akun? <a href="login.php">Login di sini</a></p>
</div>
</body>
</html>
