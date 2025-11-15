<?php
require_once __DIR__ . '/../inc/auth.php';
require_login();
require_once __DIR__ . '/../inc/db.php';

// ==== HAPUS DATA ====
if(isset($_GET['hapus'])){
    $id = intval($_GET['hapus']);
    
    $stmt = $conn->prepare("SELECT nama_barang FROM aset_kesehatan WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($nama_barang);
    $stmt->fetch();
    $stmt->close();

    if($nama_barang){
        $stmtDel = $conn->prepare("DELETE FROM kategori WHERE nama_barang=? AND kategori='Kesehatan'");
        $stmtDel->bind_param("s", $nama_barang);
        $stmtDel->execute();
        $stmtDel->close();
    }

    $stmt = $conn->prepare("DELETE FROM aset_kesehatan WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    header("Location: aset_kesehatan.php");
    exit;
}

// ==== EDIT DATA ====
$editData = null;
if(isset($_GET['edit'])){
    $id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM aset_kesehatan WHERE id=?");
    $stmt->bind_param("i",$id);
    $stmt->execute();
    $editData = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// ==== SIMPAN ====
if($_SERVER['REQUEST_METHOD']==='POST'){
    $id = intval($_POST['id'] ?? 0);
    $nama_barang = trim($_POST['nama_barang']);
    $merek = trim($_POST['merek'] ?? '');
    $no_registrasi = trim($_POST['no_registrasi']);
    $tahun_pengadaan = trim($_POST['tahun_pengadaan']);
    $lokasi = trim($_POST['lokasi']);
    $jumlah = intval($_POST['jumlah']);
    $kategori = "Kesehatan";
    $foto = $_POST['foto_lama'] ?? '';

    if(!empty($_FILES['foto_barang']['name'])){
        $ext = pathinfo($_FILES['foto_barang']['name'], PATHINFO_EXTENSION);
        $foto = time() . "_" . uniqid() . "." . $ext;
        if(!is_dir(__DIR__ . '/uploads_aset_kesehatan')) mkdir(__DIR__ . '/uploads_aset_kesehatan',0777,true);
        move_uploaded_file($_FILES['foto_barang']['tmp_name'], __DIR__."/uploads_aset_kesehatan/".$foto);
    }

    if($id>0){
        // UPDATE
        $stmt = $conn->prepare("UPDATE aset_kesehatan SET nama_barang=?, merek=?, no_registrasi=?, tahun_pengadaan=?, lokasi=?, jumlah=?, foto_barang=? WHERE id=?");
        $stmt->bind_param("ssssisis",$nama_barang,$merek,$no_registrasi,$tahun_pengadaan,$lokasi,$jumlah,$foto,$id);
        $stmt->execute();
        $stmt->close();

        $stmt2 = $conn->prepare("UPDATE kategori SET nama_barang=?, no_registrasi=?, tahun_pengadaan=?, lokasi=?, jumlah=?, foto=? WHERE nama_barang=? AND kategori='Kesehatan'");
        $stmt2->bind_param("ssssisss",$nama_barang,$no_registrasi,$tahun_pengadaan,$lokasi,$jumlah,$foto,$_POST['nama_barang_lama']);
        $stmt2->execute();
        $stmt2->close();
    }else{
        // INSERT
        $stmt = $conn->prepare("INSERT INTO aset_kesehatan (nama_barang,merek,no_registrasi,tahun_pengadaan,lokasi,jumlah,foto_barang) VALUES (?,?,?,?,?,?,?)");
        $stmt->bind_param("sssssis",$nama_barang,$merek,$no_registrasi,$tahun_pengadaan,$lokasi,$jumlah,$foto);
        $stmt->execute();
        $stmt->close();

        $stmt2 = $conn->prepare("INSERT INTO kategori (nama_barang,no_registrasi,tahun_pengadaan,lokasi,jumlah,kategori,foto) VALUES (?,?,?,?,?,?,?)");
        $stmt2->bind_param("ssssiss",$nama_barang,$no_registrasi,$tahun_pengadaan,$lokasi,$jumlah,$kategori,$foto);
        $stmt2->execute();
        $stmt2->close();
    }

    header("Location: aset_kesehatan.php");
    exit;
}

// ==== AMBIL DATA ====
$result = $conn->query("SELECT * FROM aset_kesehatan ORDER BY nama_barang ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Aset Kesehatan - SIAT</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body{background:#eef2f7;font-family:'Segoe UI',sans-serif;}
.container{margin-top:40px;}
.card{border-radius:20px;box-shadow:0 10px 25px rgba(0,0,0,0.08);}
table img{border-radius:8px;cursor:pointer;transition:.2s;}
table img:hover{transform:scale(1.1);}
h1{color:#273c75;font-weight:700;}
.modal-content{border-radius:15px;}
</style>
</head>
<body>
<div class="container">
<div class="card p-4">
<div class="d-flex justify-content-between align-items-center mb-3">
<h1>📋 Data Aset Kesehatan</h1>
<div>
<a href="index.php" class="btn btn-secondary">⬅️ Kembali</a>
<button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalForm">➕ Tambah Data</button>
</div>
</div>

<table class="table table-striped table-bordered text-center align-middle">
<thead class="table-dark">
<tr>
<th>No</th>
<th>Nama Barang</th>
<th>Merek</th>
<th>No Registrasi</th>
<th>Tahun</th>
<th>Lokasi</th>
<th>Jumlah</th>
<th>Foto</th>
<th>Aksi</th>
</tr>
</thead>
<tbody>
<?php
$no=1;
if($result && $result->num_rows>0):
while($row=$result->fetch_assoc()):
?>
<tr>
<td><?= $no++ ?></td>
<td><?= htmlspecialchars($row['nama_barang']) ?></td>
<td><?= htmlspecialchars($row['merek']) ?></td>
<td><?= htmlspecialchars($row['no_registrasi']) ?></td>
<td><?= htmlspecialchars($row['tahun_pengadaan']) ?></td>
<td><?= htmlspecialchars($row['lokasi']) ?></td>
<td><?= htmlspecialchars($row['jumlah']) ?></td>
<td>
<?php if($row['foto_barang']): ?>
<img src="uploads_aset_kesehatan/<?= htmlspecialchars($row['foto_barang']) ?>" width="80">
<?php else: ?><em>-</em><?php endif; ?>
</td>
<td>
<a href="?edit=<?= $row['id'] ?>" class="btn btn-warning btn-sm">✏️</a>
<a href="?hapus=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus data ini?')">🗑️</a>
</td>
</tr>
<?php endwhile; else: ?>
<tr><td colspan="9">Belum ada data.</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>
</div>

<!-- Modal Tambah/Edit -->
<div class="modal fade" id="modalForm" tabindex="-1">
<div class="modal-dialog">
<div class="modal-content">
<form method="POST" enctype="multipart/form-data">
<div class="modal-header">
<h5 class="modal-title"><?= $editData?'Edit Aset':'Tambah Aset' ?></h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
<input type="hidden" name="id" value="<?= $editData['id'] ?? '' ?>">
<input type="hidden" name="foto_lama" value="<?= $editData['foto_barang'] ?? '' ?>">
<input type="hidden" name="nama_barang_lama" value="<?= $editData['nama_barang'] ?? '' ?>">
<div class="mb-2"><label>Nama Barang</label><input type="text" name="nama_barang" class="form-control" value="<?= htmlspecialchars($editData['nama_barang'] ?? '') ?>" required></div>
<div class="mb-2"><label>Merek</label><input type="text" name="merek" class="form-control" value="<?= htmlspecialchars($editData['merek'] ?? '') ?>"></div>
<div class="mb-2"><label>No Registrasi</label><input type="text" name="no_registrasi" class="form-control" value="<?= htmlspecialchars($editData['no_registrasi'] ?? '') ?>"></div>
<div class="mb-2"><label>Tahun Pengadaan</label><input type="number" name="tahun_pengadaan" class="form-control" value="<?= htmlspecialchars($editData['tahun_pengadaan'] ?? '') ?>"></div>
<div class="mb-2"><label>Lokasi</label><input type="text" name="lokasi" class="form-control" value="<?= htmlspecialchars($editData['lokasi'] ?? '') ?>"></div>
<div class="mb-2"><label>Jumlah</label><input type="number" name="jumlah" class="form-control" value="<?= htmlspecialchars($editData['jumlah'] ?? '') ?>"></div>
<div class="mb-2"><label>Foto</label><input type="file" name="foto_barang" class="form-control" accept="image/*"></div>
<?php if(!empty($editData['foto_barang'])): ?>
<p class="mt-2">Foto saat ini:<br><img src="uploads_aset_kesehatan/<?= htmlspecialchars($editData['foto_barang']) ?>" width="80"></p>
<?php endif; ?>
</div>
<div class="modal-footer">
<button type="submit" class="btn btn-primary">💾 Simpan</button>
<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
</div>
</form>
</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
<?php if($editData): ?>
var modal = new bootstrap.Modal(document.getElementById('modalForm')); modal.show();
<?php endif; ?>
</script>
</body>
</html>
