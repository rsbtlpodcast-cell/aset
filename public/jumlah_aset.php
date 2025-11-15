<?php
require_once __DIR__ . '/../inc/auth.php'; 
require_login();
$user = $_SESSION['user'];

require_once __DIR__ . '/../inc/db.php';

// Cek apakah tabel aset punya kolom jumlah
$total = 0;
$sql = "SELECT COUNT(*) AS total FROM aset"; // jika tidak ada kolom jumlah
if ($result = $conn->query($sql)) {
    $row = $result->fetch_assoc();
    $total = $row['total'] ?? 0;
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Total Aset - Aplikasi Aset RSUD</title>
  <style>
    body {font-family: Arial, sans-serif; background:#f5f6fa; margin:0;}
    nav {background:#273c75; padding:12px;}
    nav a {color:white; margin:0 10px; text-decoration:none; font-weight:bold;}
    nav a:hover {text-decoration:underline;}
    main {max-width:600px; margin:auto; padding:40px 20px;}
    .card {background:white; border-radius:12px; padding:30px; text-align:center;
           box-shadow:0 4px 12px rgba(0,0,0,0.15);}
    h1 {color:#273c75;}
    .total {font-size:40px; font-weight:bold; color:#44bd32;}
  </style>
</head>
<body>
  <main>
    <div class="card">
      <h1>Total Data Aset</h1>
      <p class="total"><?= number_format($total, 0, ',', '.') ?> item</p>
    </div>
  </main>
</body>
</html>
