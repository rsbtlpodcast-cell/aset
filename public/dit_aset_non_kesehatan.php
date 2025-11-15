<?php
require_once __DIR__ . '/../inc/auth.php';
require_login();
require_once __DIR__ . '/../inc/db.php';

$id = intval($_GET['id']);
$result = $conn->query("SELECT * FROM aset_non_kesehatan WHERE id = $id");
$data = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_barang = $_POST['nama_barang'];
    $merek = $_POST['merek'];
    $no_registrasi = $_POST['no_registrasi'];
    $tahun_pengadaan = $_POST['tahun_pengadaan'];
    $lokasi = $_POST['lokasi'];

    // Update tabel aset_non_kesehatan
    $stmt = $conn->prepare("UPDATE aset_non_kesehatan SET nama_barang=?, merek=?, no_registrasi=?, tahun_pengadaan=?, lokasi=? WHERE id=?");
    $stmt->bind_param("sssssi", $nama_barang, $merek, $no_registrasi, $tahun_pengadaan, $lokasi, $id);
    $stmt->execute();

    // Update juga di tabel aset utama
    $stmt2 = $conn->prepare("UPDATE aset SET nama_barang=?, merek=?, no_registrasi=?, tahun_pengadaan=?, lokasi=? WHERE no_registrasi=?");
    $stmt2->bind_param("ssssss", $nama_barang, $merek, $no_registrasi, $tahun_pengadaan, $lokasi, $no_registrasi);
    $stmt2->execute();

    header("Location: aset_non_kesehatan.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Aset Non Kesehatan</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background: #eef2f7; font-family: 'Segoe UI', sans-serif; }
.container { margin-top: 60px; max-width: 700px; }
.card { border-radius: 20px; box-shadow: 0 8px 20px rgba(0,0,0,0.08); }
</style>
</head>
<body>
<div class="container">
<div class="card p-4">
  <h2 class="text-center mb-4 text-primary">✏️ Edit Aset Non Kesehatan</h2>
  <form method="POST">
    <div class="mb-3">
      <label class="form-label">Nama Barang</label>
      <input type="text" name="nama_barang" class="form-control" value="<?= htmlspecialchars($data['nama_barang']) ?>" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Merek</label>
      <input type="text" name="merek" class="form-control" value="<?= htmlspecialchars($data['merek']) ?>">
    </div>
    <div class="mb-3">
      <label class="form-label">No Registrasi</label>
      <input type="text" name="no_registrasi" class="form-control" value="<?= htmlspecialchars($data['no_registrasi']) ?>">
    </div>
    <div class="mb-3">
      <label class="form-label">Tahun Pengadaan</label>
      <input type="text" name="tahun_pengadaan" class="form-control" value="<?= htmlspecialchars($data['tahun_pengadaan']) ?>">
    </div>
    <div class="mb-3">
      <label class="form-label">Lokasi</label>
      <input type="text" name="lokasi" class="form-control" value="<?= htmlspecialchars($data['lokasi']) ?>">
    </div>
    <div class="d-flex justify-content-between">
      <a href="aset_non_kesehatan.php" class="btn btn-secondary">⬅️ Kembali</a>
      <button type="submit" class="btn btn-primary">💾 Simpan Perubahan</button>
    </div>
  </form>
</div>
</div>
</body>
</html>
