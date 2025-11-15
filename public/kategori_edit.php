<?php
require_once __DIR__ . '/../inc/auth.php';
require_login();
require_once __DIR__ . '/../inc/db.php';

$id = intval($_GET['id'] ?? 0);

$stmt = $conn->prepare("SELECT * FROM kategori WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$kategori = $result->fetch_assoc();

if (!$kategori) {
    die("Barang tidak ditemukan!");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_barang = trim($_POST['nama_barang']);
    $merek = trim($_POST['merek']);
    $lokasi = trim($_POST['lokasi']);

    if ($nama_barang && $merek && $lokasi) {
        $stmt = $conn->prepare("UPDATE kategori SET nama_barang=?, merek=?, lokasi=? WHERE id=?");
        $stmt->bind_param("sssi", $nama_barang, $merek, $lokasi, $id);
        $stmt->execute();
        header("Location: kategori.php");
        exit;
    } else {
        $error = "Semua field wajib diisi!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Edit Barang</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-5">
    <h1>Edit Barang</h1>
    <?php if (!empty($error)): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
    <form method="post">
        <div class="mb-3">
            <label class="form-label">Nama Barang</label>
            <input type="text" name="nama_barang" class="form-control" value="<?= htmlspecialchars($kategori['nama_barang']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Merek</label>
            <input type="text" name="merek" class="form-control" value="<?= htmlspecialchars($kategori['merek']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Lokasi</label>
            <input type="text" name="lokasi" class="form-control" value="<?= htmlspecialchars($kategori['lokasi']) ?>" required>
        </div>
        <button type="submit" class="btn btn-warning">Update</button>
        <a href="kategori.php" class="btn btn-secondary">Kembali</a>
    </form>
</body>
</html>
