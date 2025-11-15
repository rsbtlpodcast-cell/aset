<?php
require_once __DIR__ . '/../inc/auth.php';
require_login();
require_once __DIR__ . '/../inc/db.php';

$current_page = basename($_SERVER['PHP_SELF']);

// ===== TAMBAH / EDIT DATA =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $nama_barang = trim($_POST['nama_barang']);
    $merek = trim($_POST['merek'] ?? '');
    $no_registrasi = trim($_POST['no_registrasi']);
    $tahun_pengadaan = trim($_POST['tahun_pengadaan']);
    $lokasi = trim($_POST['lokasi']);
    $jumlah = intval($_POST['jumlah']);

    if ($id > 0) {
        $stmt = $conn->prepare("UPDATE aset_non_kesehatan SET nama_barang=?, merek=?, no_registrasi=?, tahun_pengadaan=?, lokasi=?, jumlah=? WHERE id=?");
        $stmt->bind_param("sssssii", $nama_barang, $merek, $no_registrasi, $tahun_pengadaan, $lokasi, $jumlah, $id);
        $stmt->execute();
        $stmt->close();
    } else {
        $stmt = $conn->prepare("INSERT INTO aset_non_kesehatan (nama_barang,merek,no_registrasi,tahun_pengadaan,lokasi,jumlah) VALUES (?,?,?,?,?,?)");
        $stmt->bind_param("sssssi", $nama_barang, $merek, $no_registrasi, $tahun_pengadaan, $lokasi, $jumlah);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: aset_non_kesehatan.php");
    exit;
}

// ===== HAPUS DATA =====
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    $del = $conn->prepare("DELETE FROM aset_non_kesehatan WHERE id=?");
    $del->bind_param("i", $id);
    $del->execute();
    $del->close();
    header("Location: aset_non_kesehatan.php");
    exit;
}

// ===== EDIT DATA =====
$editData = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM aset_non_kesehatan WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $editData = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// ===== AMBIL SEMUA DATA =====
$result = $conn->query("SELECT * FROM aset_non_kesehatan ORDER BY nama_barang ASC");
$barang_list = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Aset Non-Kesehatan - SIAT RSUD Bedas Tegalluar</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
body { font-family:'Segoe UI',sans-serif; background:#f3f6fa; display:flex; flex-direction:column; min-height:100vh; margin:0; }
.navbar { background:#273c75; box-shadow:0 3px 10px rgba(0,0,0,0.15); }
.navbar-brand, .nav-link { color:white !important; font-weight:500; transition:0.3s; }
.nav-link:hover { color:#fbc531 !important; }
.nav-link.active { color:#273c75 !important; background-color:#fbc531 !important; border-radius:6px; font-weight:600; }
.navbar-brand img { height:40px; margin-right:10px; }
.card { border-radius:20px; box-shadow:0 10px 25px rgba(0,0,0,0.08); flex:1 0 auto; }
h1 { color:#273c75; font-weight:700; }
.table thead { background:linear-gradient(90deg,#273c75,#4070f4); color:white; }
.table tbody tr:hover { background:#f1faff; transition:0.3s; }
.btn { border-radius:10px; }
.modal-content { border-radius:15px; }
footer {
  flex-shrink: 0;
  padding: 15px 0;
  text-align: center;
  background: #f1f1f1;
  color: #555;
  font-size: 0.9rem;
  margin-top: 20px;
}
</style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark">
  <div class="container-fluid">
    <a class="navbar-brand d-flex align-items-center" href="#">
      <img src="../img/logorsud.png" alt="Logo RSUD">
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link <?= $current_page=='index.php'?'active':'' ?>" href="index.php"><i class="fas fa-home"></i> Dashboard</a></li>
        <li class="nav-item"><a class="nav-link <?= $current_page=='aset.php'?'active':'' ?>" href="aset.php"><i class="fas fa-boxes"></i> Kelola Aset</a></li>
        <li class="nav-item"><a class="nav-link <?= $current_page=='kategori.php'?'active':'' ?>" href="kategori.php"><i class="fas fa-file-alt"></i> Laporan Aset</a></li>
        <li class="nav-item"><a class="nav-link <?= $current_page=='aset_kesehatan.php'?'active':'' ?>" href="aset_kesehatan.php"><i class="fas fa-pills"></i> Alkes</a></li>
        <li class="nav-item"><a class="nav-link <?= $current_page=='aset_non_kesehatan.php'?'active':'' ?>" href="aset_non_kesehatan.php"><i class="fas fa-tools"></i> Non-Alkes</a></li>
        <li class="nav-item"><a class="nav-link <?= $current_page=='alat_tulis.php'?'active':'' ?>" href="alat_tulis.php"><i class="fas fa-hand-holding"></i> Permintaan</a></li>
        <li class="nav-item"><a class="nav-link <?= $current_page=='buat_dokumen.php'?'active':'' ?>" href="buat_dokumen.php"><i class="fas fa-file-contract"></i> Buat Dokumen BA</a></li>
        <li class="nav-item"><a class="nav-link text-danger <?= $current_page=='logout.php'?'active':'' ?>" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container my-4 flex-grow-1">
  <div class="card p-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
      <h1>🛠️ Aset Non-Kesehatan</h1>
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#formModal">➕ Tambah Aset</button>
    </div>

    <input type="text" id="searchInput" class="form-control mb-3 shadow-sm" placeholder="🔍 Cari aset...">

    <div class="table-responsive">
      <table class="table table-hover text-center align-middle">
        <thead>
          <tr>
            <th>No</th>
            <th>Nama Barang</th>
            <th>Merek</th>
            <th>No Registrasi</th>
            <th>Tahun</th>
            <th>Lokasi</th>
            <th>Jumlah</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody id="asetTable">
          <?php $no=1; foreach($barang_list as $b): ?>
          <tr>
            <td><?= $no++ ?></td>
            <td><?= htmlspecialchars($b['nama_barang']) ?></td>
            <td><?= htmlspecialchars($b['merek']) ?></td>
            <td><?= htmlspecialchars($b['no_registrasi']) ?></td>
            <td><?= htmlspecialchars($b['tahun_pengadaan']) ?></td>
            <td><?= htmlspecialchars($b['lokasi']) ?></td>
            <td><?= htmlspecialchars($b['jumlah']) ?></td>
            <td>
              <a href="?edit=<?= $b['id'] ?>" class="btn btn-warning btn-sm" title="Edit"><i class="fas fa-edit"></i></a>
              <a href="?hapus=<?= $b['id'] ?>" onclick="return confirm('Yakin hapus data ini?')" class="btn btn-danger btn-sm" title="Hapus"><i class="fas fa-trash-alt"></i></a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal Tambah/Edit -->
<div class="modal fade" id="formModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header">
          <h5 class="modal-title"><?= $editData ? 'Edit Aset Non-Kesehatan' : 'Tambah Aset Non-Kesehatan' ?></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id" value="<?= $editData['id'] ?? '' ?>">
          <div class="row mb-3">
            <div class="col">
              <label>Nama Barang</label>
              <input type="text" name="nama_barang" class="form-control" required value="<?= htmlspecialchars($editData['nama_barang'] ?? '') ?>">
            </div>
            <div class="col">
              <label>Merek</label>
              <input type="text" name="merek" class="form-control" value="<?= htmlspecialchars($editData['merek'] ?? '') ?>">
            </div>
            <div class="col">
              <label>No Registrasi</label>
              <input type="text" name="no_registrasi" class="form-control" value="<?= htmlspecialchars($editData['no_registrasi'] ?? '') ?>">
            </div>
          </div>
          <div class="row mb-3">
            <div class="col">
              <label>Tahun Pengadaan</label>
              <input type="number" name="tahun_pengadaan" class="form-control" value="<?= htmlspecialchars($editData['tahun_pengadaan'] ?? '') ?>">
            </div>
            <div class="col">
              <label>Lokasi</label>
              <input type="text" name="lokasi" class="form-control" value="<?= htmlspecialchars($editData['lokasi'] ?? '') ?>">
            </div>
            <div class="col">
              <label>Jumlah</label>
              <input type="number" name="jumlah" class="form-control" value="<?= htmlspecialchars($editData['jumlah'] ?? 1) ?>">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary"><?= $editData ? '💾 Update' : '💾 Simpan' ?></button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
        </div>
      </form>
    </div>
  </div>
</div>

<footer>© <?= date('Y') ?> RSUD Bedas Tegalluar — Sistem Aset Terpadu (SIAT)</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('searchInput').addEventListener('keyup', function(){
  const filter = this.value.toLowerCase();
  document.querySelectorAll('#asetTable tr').forEach(row=>{
    row.style.display = row.textContent.toLowerCase().includes(filter)?'':'none';
  });
});
<?php if($editData): ?>
var modal = new bootstrap.Modal(document.getElementById('formModal'));
modal.show();
<?php endif; ?>
</script>
</body>
</html>
