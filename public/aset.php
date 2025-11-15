<?php
require_once __DIR__ . '/../inc/auth.php';
require_login();
require_once __DIR__ . '/../inc/db.php';
$user = $_SESSION['user'];

// === TAMBAH / EDIT DATA ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $nama_barang = trim($_POST['nama_barang']);
    $merek = trim($_POST['merek']);
    $no_registrasi = trim($_POST['no_registrasi']);
    $no_seri = trim($_POST['no_seri']);
    $tahun_pengadaan = trim($_POST['tahun_pengadaan']);
    $lokasi = trim($_POST['lokasi']);
    $jumlah = intval($_POST['jumlah']);
    $kategori = trim($_POST['kategori']); // Alkes / Non-Alkes
    $kondisi = trim($_POST['kondisi']);

    // ===== TRANSAKSI UNTUK KONSISTENSI =====
    $conn->begin_transaction();
    try {
        if ($id > 0) {
            // Edit aset
            $stmt = $conn->prepare("UPDATE aset SET nama_barang=?, merek=?, no_registrasi=?, no_seri=?, tahun_pengadaan=?, lokasi=?, jumlah=?, kategori=?, kondisi=? WHERE id=?");
            $stmt->bind_param("ssssssissi", $nama_barang, $merek, $no_registrasi, $no_seri, $tahun_pengadaan, $lokasi, $jumlah, $kategori, $kondisi, $id);
            $stmt->execute();
            $stmt->close();

            // Update kategori (bridging)
            $cat_stmt = $conn->prepare("UPDATE kategori SET nama_barang=?, merek=?, no_registrasi=?, tahun_pengadaan=?, lokasi=?, jumlah=?, kategori=? WHERE nama_barang=? AND no_registrasi=?");
            $cat_stmt->bind_param("sssssis", $nama_barang, $merek, $no_registrasi, $tahun_pengadaan, $lokasi, $jumlah, $kategori, $nama_barang, $no_registrasi);
            $cat_stmt->execute();
            $cat_stmt->close();

        } else {
            // Tambah data baru
            $stmt = $conn->prepare("INSERT INTO aset (nama_barang, merek, no_registrasi, no_seri, tahun_pengadaan, lokasi, jumlah, kategori, kondisi)
                                    VALUES (?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param("ssssssiss", $nama_barang, $merek, $no_registrasi, $no_seri, $tahun_pengadaan, $lokasi, $jumlah, $kategori, $kondisi);
            $stmt->execute();
            $stmt->close();

            // Bridging otomatis ke kategori
            $kategori_stmt = $conn->prepare("INSERT INTO kategori (nama_barang, merek, no_registrasi, tahun_pengadaan, lokasi, jumlah, kategori)
                                              VALUES (?, ?, ?, ?, ?, ?, ?)");
            $kategori_stmt->bind_param("sssssis", $nama_barang, $merek, $no_registrasi, $tahun_pengadaan, $lokasi, $jumlah, $kategori);
            $kategori_stmt->execute();
            $kategori_stmt->close();

            // ===== OTOMATIS KURANGI JUMLAH DI aset_kesehatan / aset_non_kesehatan =====
            if ($kategori === 'Alkes') {
                $updateStok = $conn->prepare("UPDATE aset_kesehatan SET jumlah = GREATEST(jumlah - ?, 0) WHERE nama_barang = ?");
                $updateStok->bind_param("is", $jumlah, $nama_barang);
                $updateStok->execute();
                $updateStok->close();
            } elseif ($kategori === 'Non-Alkes') {
                $updateStok = $conn->prepare("UPDATE aset_non_kesehatan SET jumlah = GREATEST(jumlah - ?, 0) WHERE nama_barang = ?");
                $updateStok->bind_param("is", $jumlah, $nama_barang);
                $updateStok->execute();
                $updateStok->close();
            }
        }

        $conn->commit();
    } catch (Exception $e) {
        $conn->rollback();
        die("Terjadi kesalahan: " . $e->getMessage());
    }

    header("Location: aset.php");
    exit;
}

// === HAPUS DATA ===
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    $stmt = $conn->prepare("DELETE FROM aset WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: aset.php");
    exit;
}

// === AMBIL SEMUA DATA ASET ===
$result = $conn->query("SELECT * FROM aset ORDER BY lokasi ASC, nama_barang ASC");
$aset_list = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

// Statistik
$total_aset = count($aset_list);
$total_baru = count(array_filter($aset_list, fn($a) => $a['kondisi'] == 'Baru'));
$total_rusak = count(array_filter($aset_list, fn($a) => $a['kondisi'] == 'Rusak'));
$percent_baru = $total_aset ? round(($total_baru / $total_aset) * 100) : 0;
$percent_rusak = $total_aset ? round(($total_rusak / $total_aset) * 100) : 0;

$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kelola Aset - SIAT RSUD Bedas Tegalluar</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
/* --- STYLING SAMA PERSIS --- */
html, body { height:100%; margin:0; display:flex; flex-direction:column; font-family:'Segoe UI',sans-serif; background:#f3f6fa; }
.container { flex:1 0 auto; margin-top:30px; }
.navbar { background:#273c75; box-shadow:0 3px 10px rgba(0,0,0,0.15); }
.navbar-brand, .nav-link { color:white !important; font-weight:500; transition:0.3s; }
.nav-link:hover { color:#fbc531 !important; }
.nav-link.active { color:#273c75 !important; background:#fbc531 !important; border-radius:6px; font-weight:600; }
.navbar-brand img { height:40px; margin-right:10px; }
.card { border-radius:20px; box-shadow:0 6px 20px rgba(0,0,0,0.08); }
.table thead { background:linear-gradient(90deg,#273c75,#4070f4); color:white; }
.table tbody tr:hover { background-color:#f1faff; transition:0.3s; }
.badge-gradient { color:#fff; padding:0.5em 0.75em; border-radius:12px; font-weight:bold; font-size:0.9em; }
.badge-baru { background:linear-gradient(45deg,#28a745,#5cd65c); }
.badge-rusak { background:linear-gradient(45deg,#dc3545,#ff6b6b); }
footer { flex-shrink:0; text-align:center; padding:15px; background:#f1f1f1; color:#555; margin-top:40px; }
.btn-action { border-radius:10px; padding:5px 10px; font-size:0.85rem; }
@media print { .navbar, footer, .btn-action, #btnTambah { display:none !important; } }
</style>
</head>
<body>

<!-- NAVBAR SAMA PERSIS -->
<nav class="navbar navbar-expand-lg navbar-dark sticky-top">
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
        <li class="nav-item"><a class="nav-link text-danger" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
      </ul>
    </div>
  </div>
</nav>

<!-- KONTEN -->
<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="mb-0">📦 Kelola Aset</h1>
    <button id="btnTambah" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAset">
      <i class="fas fa-plus"></i> Tambah Aset Baru
    </button>
  </div>

  <!-- Statistik -->
  <div class="row g-3 mb-4">
    <div class="col-md-4">
      <div class="card text-center text-white" style="background:#273c75;">
        <div class="card-body"><h5>Total Aset</h5><h2><?= $total_aset ?></h2></div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card text-center text-white" style="background:#28a745;">
        <div class="card-body"><h5>Aset Baru</h5><h2><?= $total_baru ?> (<?= $percent_baru ?>%)</h2></div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card text-center text-white" style="background:#dc3545;">
        <div class="card-body"><h5>Aset Rusak</h5><h2><?= $total_rusak ?> (<?= $percent_rusak ?>%)</h2></div>
      </div>
    </div>
  </div>

  <!-- Daftar Aset -->
  <div class="card p-3">
    <div class="table-responsive">
      <table class="table table-hover text-center align-middle">
        <thead>
          <tr>
            <th>No</th>
            <th>Nama Barang</th>
            <th>Merek</th>
            <th>No Registrasi</th>
            <th>No Seri</th>
            <th>Tahun</th>
            <th>Lokasi</th>
            <th>Jumlah</th>
            <th>Kategori</th>
            <th>Kondisi</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php $no=1; foreach($aset_list as $a): ?>
          <tr>
            <td><?= $no++ ?></td>
            <td><?= htmlspecialchars($a['nama_barang']) ?></td>
            <td><?= htmlspecialchars($a['merek']) ?></td>
            <td><?= htmlspecialchars($a['no_registrasi']) ?></td>
            <td><?= htmlspecialchars($a['no_seri']) ?></td>
            <td><?= htmlspecialchars($a['tahun_pengadaan']) ?></td>
            <td><?= htmlspecialchars($a['lokasi']) ?></td>
            <td><?= htmlspecialchars($a['jumlah']) ?></td>
            <td><?= htmlspecialchars($a['kategori']) ?></td>
            <td>
              <span class="badge-gradient <?= $a['kondisi']=='Baru'?'badge-baru':'badge-rusak' ?>">
                <?= htmlspecialchars($a['kondisi']) ?>
              </span>
            </td>
            <td>
              <button 
                class="btn btn-sm btn-warning text-dark btn-action btnEdit"
                data-id="<?= $a['id'] ?>"
                data-nama_barang="<?= htmlspecialchars($a['nama_barang']) ?>"
                data-merek="<?= htmlspecialchars($a['merek']) ?>"
                data-no_registrasi="<?= htmlspecialchars($a['no_registrasi']) ?>"
                data-no_seri="<?= htmlspecialchars($a['no_seri']) ?>"
                data-tahun_pengadaan="<?= htmlspecialchars($a['tahun_pengadaan']) ?>"
                data-lokasi="<?= htmlspecialchars($a['lokasi']) ?>"
                data-jumlah="<?= htmlspecialchars($a['jumlah']) ?>"
                data-kategori="<?= htmlspecialchars($a['kategori']) ?>"
                data-kondisi="<?= htmlspecialchars($a['kondisi']) ?>"
                data-bs-toggle="modal"
                data-bs-target="#modalAset">
                <i class="fas fa-edit"></i> Edit
              </button>
              <a href="cetak_label.php?id=<?= $a['id'] ?>" class="btn btn-sm btn-info text-white btn-action">
                <i class="fas fa-tag"></i> Cetak Label
              </a>
              <a href="aset.php?hapus=<?= $a['id'] ?>" class="btn btn-sm btn-danger btn-action"
                 onclick="return confirm('Yakin ingin menghapus data ini?')">
                <i class="fas fa-trash"></i> Hapus
              </a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal Tambah/Edit Aset -->
<div class="modal fade" id="modalAset" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" class="needs-validation" novalidate>
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="modalTitle">Tambah Aset Baru</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id" id="aset_id">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Nama Barang</label>
              <input type="text" name="nama_barang" id="nama_barang" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Merek</label>
              <input type="text" name="merek" id="merek" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">No Registrasi</label>
              <input type="text" name="no_registrasi" id="no_registrasi" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label">No Seri</label>
              <input type="text" name="no_seri" id="no_seri" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label">Tahun Pengadaan</label>
              <input type="number" name="tahun_pengadaan" id="tahun_pengadaan" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label">Lokasi</label>
              <input type="text" name="lokasi" id="lokasi" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label">Jumlah</label>
              <input type="number" name="jumlah" id="jumlah" class="form-control" min="1" value="1" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Kategori</label>
              <select name="kategori" id="kategori" class="form-select" required>
                <option value="">-- Pilih Kategori --</option>
                <option value="Alkes">Alkes</option>
                <option value="Non-Alkes">Non-Alkes</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Kondisi</label>
              <select name="kondisi" id="kondisi" class="form-select" required>
                <option value="">-- Pilih Kondisi --</option>
                <option value="Baru">Baru</option>
                <option value="Rusak">Rusak</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<footer>
  © <?= date('Y') ?> RSUD Bedas Tegalluar - Sistem Aset Terpadu (SIAT)
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  const modal = document.getElementById('modalAset');
  const title = document.getElementById('modalTitle');
  const idField = document.getElementById('aset_id');
  const form = modal.querySelector('form');
  
  document.getElementById('btnTambah').addEventListener('click', () => {
    title.textContent = 'Tambah Aset Baru';
    form.reset();
    idField.value = '';
  });

  document.querySelectorAll('.btnEdit').forEach(btn => {
    btn.addEventListener('click', () => {
      title.textContent = 'Edit Aset';
      idField.value = btn.dataset.id;
      document.getElementById('nama_barang').value = btn.dataset.nama_barang;
      document.getElementById('merek').value = btn.dataset.merek;
      document.getElementById('no_registrasi').value = btn.dataset.no_registrasi;
      document.getElementById('no_seri').value = btn.dataset.no_seri;
      document.getElementById('tahun_pengadaan').value = btn.dataset.tahun_pengadaan;
      document.getElementById('lokasi').value = btn.dataset.lokasi;
      document.getElementById('jumlah').value = btn.dataset.jumlah;
      document.getElementById('kategori').value = btn.dataset.kategori;
      document.getElementById('kondisi').value = btn.dataset.kondisi;
    });
  });
});
</script>
</body>
</html>
