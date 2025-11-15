<?php
require_once __DIR__ . '/../inc/auth.php';
require_login();
$user = $_SESSION['user'];

require_once __DIR__ . '/../inc/db.php'; // pastikan $conn tersedia

// Ambil daftar lokasi
$lokasi_list = [];
$sql = "SELECT * FROM lokasi ORDER BY id DESC";
if ($result = $conn->query($sql)) {
    $lokasi_list = $result->fetch_all(MYSQLI_ASSOC);
}

// Hapus lokasi jika ada parameter ?hapus=id
if (isset($_GET['hapus'])) {
    $id_hapus = (int)$_GET['hapus'];
    $conn->query("DELETE FROM lokasi WHERE id=$id_hapus");
    header("Location: lokasi.php");
    exit;
}
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Kelola Lokasi - Aplikasi Aset RSUD</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { font-family:'Segoe UI', sans-serif; background:#f4f7fa; margin:0; color:#333; }
main { max-width:900px; margin:50px auto; padding:0 20px; }
.card { background:#fff; border-radius:16px; padding:25px; text-align:center; margin-bottom:30px; box-shadow:0 6px 20px rgba(0,0,0,0.1); }
.card h1 { color:#007bff; }
.table-card { background:#fff; border-radius:16px; padding:20px; box-shadow:0 6px 20px rgba(0,0,0,0.1); }
.table-card h2 { color:#007bff; margin-bottom:20px; }
.btn-custom { border-radius:12px; }
.table-responsive { overflow-x:auto; }
</style>
</head>
<body>
<main>
    <div class="card">
        <h1>Kelola Lokasi</h1>
        <p>Selamat datang, <?= htmlspecialchars($user['username']) ?> (<?= htmlspecialchars($user['role']) ?>)</p>
        <p>Tambah, edit, dan hapus lokasi aset rumah sakit.</p>
    </div>

    <div class="table-card">
        <h2>Daftar Lokasi</h2>
        <a href="lokasi_tambah.php" class="btn btn-primary btn-custom mb-3">➕ Tambah Lokasi</a>
        <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-primary">
                <tr>
                    <th>ID</th>
                    <th>Nama Lokasi</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($lokasi_list)): ?>
                    <?php foreach ($lokasi_list as $lokasi): ?>
                        <tr>
                            <td><?= $lokasi['id'] ?></td>
                            <td><?= htmlspecialchars($lokasi['nama']) ?></td>
                            <td>
                                <a href="lokasi_edit.php?id=<?= $lokasi['id'] ?>" class="btn btn-sm btn-warning btn-custom">✏️ Edit</a>
                                <a href="lokasi.php?hapus=<?= $lokasi['id'] ?>" class="btn btn-sm btn-danger btn-custom" onclick="return confirm('Yakin ingin hapus?')">🗑️ Hapus</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" class="text-center">Belum ada data lokasi</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>
</main>
</body>
</html>
