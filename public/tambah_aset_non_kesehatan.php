<?php
require_once __DIR__ . '/../inc/auth.php';
require_login();
require_once __DIR__ . '/../inc/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_barang = trim($_POST['nama_barang'] ?? '');
    $merk_tipe = trim($_POST['merk_tipe'] ?? '');
    $lokasi = trim($_POST['lokasi'] ?? '');
    $jumlah = intval($_POST['jumlah'] ?? 0);

    $foto = null;
    if (!empty($_FILES['foto']['name'])) {
        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $foto = time() . "_" . uniqid() . "." . $ext;
        move_uploaded_file($_FILES['foto']['tmp_name'], __DIR__ . "/uploads_aset/" . $foto);
    }

    $stmt = $conn->prepare("INSERT INTO aset_non_kesehatan (nama_barang, merk_tipe, lokasi, jumlah, foto) VALUES (?,?,?,?,?)");
    $stmt->bind_param("sssis",$nama_barang,$merk_tipe,$lokasi,$jumlah,$foto);
    $stmt->execute();
    $stmt->close();

    // Tambahkan juga ke kategori
    $cat = $conn->prepare("INSERT INTO kategori (nama_barang, kategori, lokasi, jumlah, foto) VALUES (?, 'Non Kesehatan', ?, ?, ?)");
    $cat->bind_param("ssis",$nama_barang,$lokasi,$jumlah,$foto);
    $cat->execute();
    $cat->close();

    header("Location: aset_non_kesehatan.php");
    exit;
}
?>
<!-- tampilan form tidak diubah -->

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tambah Aset Non Kesehatan - SIAT RSUD Bedas Tegalluar</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background: #eef2f7; font-family: 'Segoe UI', sans-serif; }
.container { margin-top: 50px; max-width: 800px; }
.card { border-radius: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.08); }
h1 { color: #273c75; font-weight: 700; }
button, .btn { border-radius: 10px; }
</style>
</head>
<body>
<div class="container">
<div class="card p-4">
  <h1 class="mb-4 text-center">➕ Tambah Aset Non Kesehatan</h1>
  <form method="POST" enctype="multipart/form-data">
    <div class="row mb-3">
      <div class="col">
        <label class="form-label">Nama Barang</label>
        <input type="text" name="nama_barang" class="form-control" required>
      </div>
      <div class="col">
        <label class="form-label">Merek</label>
        <input type="text" name="merek" class="form-control">
      </div>
    </div>

    <div class="row mb-3">
      <div class="col">
        <label class="form-label">No Registrasi</label>
        <input type="text" name="no_registrasi" class="form-control">
      </div>
      <div class="col">
        <label class="form-label">Tahun Pengadaan</label>
        <input type="number" name="tahun_pengadaan" maxlength="4" class="form-control">
      </div>
    </div>

    <div class="row mb-4">
      <div class="col">
        <label class="form-label">Lokasi</label>
        <input type="text" name="lokasi" class="form-control">
      </div>
      <div class="col">
        <label class="form-label">Foto Barang</label>
        <input type="file" name="foto" accept="image/*" class="form-control">
      </div>
    </div>

    <div class="d-flex justify-content-between">
      <a href="aset_non_kesehatan.php" class="btn btn-outline-secondary">⬅️ Kembali</a>
      <button type="submit" class="btn btn-primary">💾 Simpan</button>
    </div>
  </form>
</div>
</div>
</body>
</html>
