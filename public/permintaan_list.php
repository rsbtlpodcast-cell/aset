<?php
require_once __DIR__ . '/../inc/auth.php';
require_login();
require_once __DIR__ . '/../inc/db.php';
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Daftar Permintaan ATK</title>
</head>
<body>
  <h2>Daftar Permintaan ATK</h2>
  <table border="1" cellpadding="6" cellspacing="0">
    <tr>
      <th>No</th>
      <th>Nama Pemohon</th>
      <th>Instalasi</th>
      <th>Nama Barang</th>
      <th>Jumlah</th>
      <th>Keterangan</th>
      <th>Status</th>
      <th>Aksi</th>
    </tr>
    <?php
    $result = $db->query("SELECT * FROM permintaan_atk ORDER BY created_at DESC");
    $no=1;
    while ($row = $result->fetch_assoc()):
    ?>
    <tr>
      <td><?= $no++ ?></td>
      <td><?= htmlspecialchars($row['nama_pemohon']) ?></td>
      <td><?= htmlspecialchars($row['instalasi']) ?></td>
      <td><?= htmlspecialchars($row['nama_barang']) ?></td>
      <td><?= $row['jumlah'] ?></td>
      <td><?= htmlspecialchars($row['keterangan']) ?></td>
      <td><?= $row['status'] ?></td>
      <td>
        <?php if ($row['status']=='pending'): ?>
          <a href="update_status.php?id=<?= $row['id'] ?>&status=diproses">Proses</a>
        <?php elseif ($row['status']=='diproses'): ?>
          <a href="update_status.php?id=<?= $row['id'] ?>&status=selesai">Selesai</a>
        <?php else: ?>
          -
        <?php endif; ?>
      </td>
    </tr>
    <?php endwhile; ?>
  </table>
</body>
</html>
