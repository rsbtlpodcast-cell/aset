<?php
require_once __DIR__ . '/../inc/auth.php';
require_login();
$user = $_SESSION['user'];
require_once __DIR__ . '/../inc/db.php';

// Hitung jumlah permintaan pending
$notif = $db->query("SELECT COUNT(*) as jml FROM permintaan_atk WHERE status='pending'")->fetch_assoc();
$jml_notif = $notif['jml'];
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Admin ATK - Aplikasi Aset RSUD</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <h2>Halaman Admin ATK</h2>

  <div style="margin:10px 0;">
    <a href="permintaan_list.php">
      Permintaan ATK 
      <?php if ($jml_notif > 0): ?>
        <span style="color:white; background:red; padding:3px 8px; border-radius:50%;">
          <?= $jml_notif ?>
        </span>
      <?php endif; ?>
    </a>
  </div>

  <h3>Data ATK</h3>
  <table border="1" cellpadding="6" cellspacing="0">
    <tr>
      <th>No</th>
      <th>Nama</th>
      <th>Kode</th>
      <th>Kondisi</th>
    </tr>
    <?php
    $result = $db->query("SELECT * FROM aset ORDER BY id DESC");
    $no=1;
    while ($row = $result->fetch_assoc()):
    ?>
    <tr>
      <td><?= $no++ ?></td>
      <td><?= htmlspecialchars($row['nama']) ?></td>
      <td><?= htmlspecialchars($row['kode']) ?></td>
      <td><?= htmlspecialchars($row['kondisi']) ?></td>
    </tr>
    <?php endwhile; ?>
  </table>
</body>
</html>
