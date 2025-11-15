<?php
require_once __DIR__ . '/../inc/auth.php';
require_login();
require_once __DIR__ . '/../inc/db.php';

$id = $_GET['id'] ?? 0;
$id = intval($id);
$nama = $merk = $lokasi = "";
$jumlah = 1;

// Ambil data jika edit
if ($id > 0) {
    $stmt = $conn->prepare("SELECT * FROM aset_kesehatan WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    if ($result) {
        $nama = $result['nama_alat'];
        $merk = $result['merk_tipe'];
        $lokasi = $result['lokasi'];
        $jumlah = $result['jumlah'];
    }
    $stmt->close();
}

// Simpan data (insert/update)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama_alat'];
    $merk = $_POST['merk_tipe'];
    $lokasi = $_POST['lokasi'];
    $jumlah = intval($_POST['jumlah']);

    if ($id > 0) {
        $stmt = $conn->prepare("UPDATE aset_kesehatan SET nama_alat=?, merk_tipe=?, lokasi=?, jumlah=? WHERE id=?");
        $stmt->bind_param("sssii", $nama, $merk, $lokasi, $jumlah, $id);
    } else {
        $stmt = $conn->prepare("INSERT INTO aset_kesehatan (nama_alat, merk_tipe, lokasi, jumlah) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $nama, $merk, $lokasi, $jumlah);
    }
    $stmt->execute();
    $stmt->close();
    header("Location: aset_kesehatan.php");
    exit;
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title><?= $id > 0 ? "Edit" : "Tambah" ?> Aset Kesehatan</title>
  <style>
    body {font-family:'Segoe UI',sans-serif; background:#f0f2f5; padding:20px;}
    .card {background:#fff; padding:20px; border-radius:12px; box-shadow:0 4px 10px rgba(0,0,0,.1);}
    h1 {color:#2f3640;}
    .btn-back {
      display:inline-block; padding:8px 14px; margin-bottom:15px;
      background:#718093; color:#fff; text-decoration:none;
      border-radius:6px; font-size:14px;
    }
    .btn-back:hover {background:#636e72;}
    label {font-weight:600; display:block; margin-top:10px;}
    input {width:100%; padding:10px; margin-top:5px; border:1px solid #ddd; border-radius:6px;}
    button {margin-top:15px; padding:10px; width:100%; border:none; border-radius:8px; background:#273c75; color:#fff; font-weight:bold; cursor:pointer;}
    button:hover {background:#192a56;}
  </style>
</head>
<body>
  <div class="card">
    <h1><?= $id > 0 ? "✏️ Edit" : "➕ Tambah" ?> Aset Kesehatan</h1>

    <!-- Tombol Kembali -->
    <a href="aset_kesehatan.php" class="btn-back">⬅️ Kembali ke Daftar Aset</a>

    <form method="post">
      <label>Nama Alat</label>
      <input type="text" name="nama_alat" value="<?= htmlspecialchars($nama) ?>" required>
      <label>Merk/Tipe</label>
      <input type="text" name="merk_tipe" value="<?= htmlspecialchars($merk) ?>">
      <label>Lokasi</label>
      <input type="text" name="lokasi" value="<?= htmlspecialchars($lokasi) ?>">
      <label>Jumlah</label>
      <input type="number" name="jumlah" value="<?= $jumlah ?>" min="1" required>
      <button type="submit">Simpan</button>
    </form>
  </div>
</body>
</html>
