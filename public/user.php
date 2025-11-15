<?php
require_once __DIR__ . '/../inc/auth.php';
require_login();
$user = $_SESSION['user'];
require_once __DIR__ . '/../inc/db.php';
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Halaman User - Aplikasi Aset RSUD</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <h2>Daftar Aset RSUD</h2>
  <table border="1" cellpadding="6" cellspacing="0">
    <tr>
      <th>No</th>
      <th>Nama Aset</th>
      <th>Kode</th>
      <th>Kondisi</th>
    </tr>
    <?php
    $result = $db->query("SELECT id, nama, kode, kondisi FROM aset ORDER BY id DESC");
    $no = 1;
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

  <h2>Form Permintaan ATK</h2>
  <form action="permintaan_atk.php" method="post">
    <label>Nama Pemohon:</label><br>
    <input type="text" name="nama_pemohon" required><br><br>

    <label>Instalasi:</label><br>
    <input type="text" name="instalasi" required><br><br>

    <label>Nama Barang:</label><br>
    <input type="text" name="nama_barang" required><br><br>

    <label>Jumlah:</label><br>
    <input type="number" name="jumlah" required><br><br>

    <label>Keterangan (opsional):</label><br>
    <textarea name="keterangan"></textarea><br><br>

    <button type="submit" name="submit">Kirim Permintaan</button>
  </form>
</body>
</html>
