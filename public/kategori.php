<?php
require_once __DIR__ . '/../inc/auth.php';
require_login();
require_once __DIR__ . '/../inc/db.php';
$user = $_SESSION['user'];

// ===== HAPUS DATA =====
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    $stmt = $conn->prepare("DELETE FROM kategori WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: kategori.php?msg=deleted");
    exit;
}

// ===== AMBIL SEMUA DATA KATEGORI =====
$stmt = $conn->prepare("SELECT * FROM kategori ORDER BY nama_barang ASC");
$stmt->execute();
$barang_list = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kategori Aset - SIAT RSUD Bedas Tegalluar</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
/* ===== Body & Layout ===== */
html, body { height:100%; margin:0; display:flex; flex-direction:column; font-family:'Segoe UI',sans-serif; background:#f5f6fa; color:#2f3640; }
nav { background:#273c75; padding:12px 25px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; box-shadow:0 2px 6px rgba(0,0,0,0.2); position:sticky; top:0; z-index:1000; }
nav .logo { display:flex; align-items:center; color:white; font-weight:bold; font-size:20px; }
nav .logo img { height:40px; margin-right:10px; }
nav .menu { display:flex; gap:15px; flex-wrap:wrap; }
nav .menu a { color:white; text-decoration:none; font-weight:500; padding:8px 12px; border-radius:6px; transition:0.3s; }
nav .menu a:hover, nav .menu a.active { background:#fbc531; color:#273c75; }
.container-main { flex:1 0 auto; padding:30px; }
.card { border-radius:20px; box-shadow:0 8px 20px rgba(0,0,0,0.08); padding:25px; }
.table thead { background:linear-gradient(90deg,#0d6efd,#4dabf7); color:white; }
.table tbody tr:hover { background-color:#f1faff; transition:0.3s; }
footer { flex-shrink:0; padding:15px 0; text-align:center; background:#f1f1f1; color:#555; }
@media (max-width:768px){ nav{flex-direction:column;align-items:flex-start;} nav .menu{flex-wrap:wrap;} }
</style>
</head>
<body>

<!-- Navbar -->
<nav>
  <div class="logo">
    <img src="../img/logorsud.png" alt="Logo RSUD"> 
  </div>
  <div class="menu">
    <a href="index.php"><i class="fas fa-home"></i> Dashboard</a>
    <a href="aset.php"><i class="fas fa-boxes"></i> Kelola Aset</a>
    <a href="kategori.php" class="active"><i class="fas fa-file-alt"></i> Laporan Aset</a>
    <a href="aset_kesehatan.php"><i class="fas fa-pills"></i> Alkes</a>
    <a href="aset_non_kesehatan.php"><i class="fas fa-tools"></i> Non-Alkes</a>
    <a href="alat_tulis.php"><i class="fas fa-hand-holding"></i> Permintaan</a>
    <a href="buat_dokumen.php"><i class="fas fa-file-contract"></i> Buat Dokumen BA</a>
    <a href="logout.php" onclick="return confirm('Yakin ingin logout?')"><i class="fas fa-sign-out-alt"></i> Logout</a>
  </div>
</nav>

<div class="container-main">
<div class="card">

  <!-- Notifikasi -->
  <?php if(isset($_GET['msg'])): ?>
    <?php $alert=['added'=>'✅ Data berhasil ditambahkan!','updated'=>'✏️ Data berhasil diperbarui!','deleted'=>'🗑️ Data berhasil dihapus!']; ?>
    <div class="alert alert-success alert-dismissible fade show"><?= $alert[$_GET['msg']] ?? '' ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <!-- Filter & Pencarian -->
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
    <div class="filter-btns btn-group">
      <button class="btn btn-primary" onclick="filterKategori('Semua')"><i class="fas fa-list"></i> Semua</button>
      <button class="btn btn-outline-success" onclick="filterKategori('Alkes')"><i class="fas fa-pills"></i> Kesehatan</button>
      <button class="btn btn-outline-primary" onclick="filterKategori('Non-Alkes')"><i class="fas fa-tools"></i> Non Kesehatan</button>
    </div>
    <input type="text" id="searchInput" class="form-control w-auto shadow-sm" placeholder="🔍 Cari barang...">
  </div>

<!-- Tombol Cetak -->
<div class="mb-3 text-end">
  <a href="cetak_laporan.php" target="_blank" class="btn btn-danger shadow-sm">
    <i class="fas fa-print"></i> Cetak Laporan
  </a>
</div>
  <!-- Tabel Data -->
  <div class="table-responsive">
  <table class="table table-striped table-hover text-center align-middle" id="barangTable">
    <thead>
      <tr>
        <th>No</th>
        <th>Nama Barang</th>
        <th>Merek</th>
        <th>No Registrasi</th>
        <th>Tahun Pengadaan</th>
        <th>Lokasi</th>
        <th>Jumlah</th>
        <th>Kategori</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php if(empty($barang_list)): ?>
        <tr><td colspan="9" class="text-muted py-4">Belum ada data kategori.</td></tr>
      <?php else: $no=1; foreach($barang_list as $b): ?>
        <tr data-kategori="<?= htmlspecialchars($b['kategori']) ?>">
          <td><?= $no++ ?></td>
          <td><?= htmlspecialchars($b['nama_barang']) ?></td>
          <td><?= htmlspecialchars($b['merek']) ?></td>
          <td><?= htmlspecialchars($b['no_registrasi']) ?></td>
          <td><?= htmlspecialchars($b['tahun_pengadaan']) ?></td>
          <td><?= htmlspecialchars($b['lokasi']) ?></td>
          <td><?= htmlspecialchars($b['jumlah']) ?></td>
          <td>
            <span class="badge bg-<?= $b['kategori']=='Alkes'?'success':'primary' ?>"><?= htmlspecialchars($b['kategori']) ?></span>
          </td>
          <td>
            <a href="?hapus=<?= $b['id'] ?>" onclick="return confirm('Hapus data ini?')" class="btn btn-danger btn-sm">🗑️</a>
          </td>
        </tr>
      <?php endforeach; endif; ?>
    </tbody>
  </table>
  </div>

</div>
</div>

<footer>
  © <?= date('Y') ?> RSUD Bedas Tegalluar - Sistem Aset Terpadu (SIAT)
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Pencarian realtime
document.getElementById('searchInput').addEventListener('keyup', function(){
  const filter = this.value.toLowerCase();
  document.querySelectorAll('#barangTable tbody tr').forEach(row=>{
    row.style.display = row.textContent.toLowerCase().includes(filter)?'':'none';
  });
});

// Filter kategori + tombol aktif
function filterKategori(kategori){
  document.querySelectorAll('#barangTable tbody tr').forEach(row=>{
    const rowKategori = row.getAttribute('data-kategori');
    row.style.display = (kategori==='Semua' || rowKategori===kategori)?'':'none';
  });
  document.querySelectorAll('.filter-btns .btn').forEach(btn=>{
    btn.classList.remove('btn-primary');
    btn.classList.add('btn-outline-primary','btn-outline-success');
  });
  const activeBtn = Array.from(document.querySelectorAll('.filter-btns .btn')).find(btn => btn.textContent.includes(kategori==='Alkes'?'Kesehatan':kategori==='Non-Alkes'?'Non Kesehatan':kategori));
  if(activeBtn){
    activeBtn.classList.remove('btn-outline-primary','btn-outline-success');
    activeBtn.classList.add('btn-primary');
  }
}
</script>
</body>
</html>
