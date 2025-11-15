<?php
require_once __DIR__ . '/../inc/auth.php';
require_login();
$user = $_SESSION['user'];
require_once __DIR__ . '/../inc/db.php';

$current_page = basename($_SERVER['PHP_SELF']);

// ===== TAMBAH ATK =====
if (isset($_POST['add'])) {
    $stmt = $conn->prepare("INSERT INTO alat_tulis (nama, kode, kondisi, tahun, jumlah) VALUES (?,?,?,?,?)");
    $stmt->bind_param("ssssi", $_POST['nama'], $_POST['kode'], $_POST['kondisi'], $_POST['tahun'], $_POST['jumlah']);
    $stmt->execute();
    header("Location: alat_tulis.php");
    exit;
}

// ===== EDIT ATK =====
if (isset($_POST['edit'])) {
    $stmt = $conn->prepare("UPDATE alat_tulis SET nama=?, kode=?, kondisi=?, tahun=?, jumlah=? WHERE id=?");
    $stmt->bind_param("sssiii", $_POST['nama'], $_POST['kode'], $_POST['kondisi'], $_POST['tahun'], $_POST['jumlah'], $_POST['id']);
    $stmt->execute();
    header("Location: alat_tulis.php");
    exit;
}

// ===== HAPUS ATK =====
if (isset($_GET['delete'])) {
    $stmt = $conn->prepare("DELETE FROM alat_tulis WHERE id=?");
    $stmt->bind_param("i", $_GET['delete']);
    $stmt->execute();
    header("Location: alat_tulis.php");
    exit;
}

// ===== UPDATE BATCH =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['batch_items'])) {
    $items = json_decode($_POST['batch_items'], true);
    if (!$items) exit(json_encode(['success' => false, 'msg' => 'Data kosong']));
    $conn->begin_transaction();
    try {
        $stmt_update = $conn->prepare("UPDATE alat_tulis SET jumlah=? WHERE id=?");
        $stmt_hist = $conn->prepare("INSERT INTO atk_histori (id_atk, nama_atk, instalasi, pemohon, jumlah_permintaan, jumlah_realisasi) VALUES (?,?,?,?,?,?)");
        foreach ($items as $it) {
            $id = $it['id'];
            $res = $conn->query("SELECT jumlah,nama FROM alat_tulis WHERE id=$id");
            $row = $res->fetch_assoc();
            if (!$row) throw new Exception("Item ID $id tidak ditemukan");
            $stok_baru = $row['jumlah'] - $it['realisasi'];
            if ($stok_baru < 0) throw new Exception("Stok tidak cukup untuk " . $row['nama']);
            $stmt_update->bind_param("ii", $stok_baru, $id);
            $stmt_update->execute();
            $stmt_hist->bind_param("isssii", $id, $row['nama'], $it['instalasi'], $it['pemohon'], $it['permintaan'], $it['realisasi']);
            $stmt_hist->execute();
        }
        $conn->commit();
        $histori_new = [];
        foreach ($items as $it) {
            $res = $conn->query("SELECT * FROM atk_histori WHERE id_atk={$it['id']} ORDER BY tanggal DESC LIMIT 1");
            $histori_new[] = $res->fetch_assoc();
        }
        exit(json_encode(['success' => true, 'histori' => $histori_new]));
    } catch (Exception $e) {
        $conn->rollback();
        exit(json_encode(['success' => false, 'msg' => $e->getMessage()]));
    }
}

$result = $conn->query("SELECT * FROM alat_tulis ORDER BY nama ASC");
$histori = $conn->query("
    SELECT h.*, a.kode, a.kondisi, a.tahun 
    FROM atk_histori h 
    LEFT JOIN alat_tulis a ON a.id=h.id_atk 
    ORDER BY h.tanggal DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Alat Tulis Kantor - SIAT RSUD Bedas Tegalluar</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/html2pdf.js@0.10.1/dist/html2pdf.bundle.min.js"></script>
<style>
body{font-family:'Segoe UI',sans-serif;background:#f3f6fa;display:flex;flex-direction:column;min-height:100vh;margin:0;}
.navbar{background:#273c75;box-shadow:0 3px 10px rgba(0,0,0,0.15);}
.navbar-brand,.nav-link{color:white !important;font-weight:500;transition:0.3s;}
.nav-link:hover{color:#fbc531 !important;}
.nav-link.active{color:#273c75 !important;background-color:#fbc531 !important;border-radius:6px;font-weight:600;}
.card{border-radius:20px;box-shadow:0 10px 25px rgba(0,0,0,0.08);}
.table thead{background:linear-gradient(90deg,#273c75,#4070f4);color:white;}
.table tbody tr:hover{background:#f1faff;}
.btn{border-radius:10px;}
.modal-content{border-radius:15px;}
footer{flex-shrink:0;padding:15px 0;text-align:center;background:#f1f1f1;color:#555;margin-top:auto;}
@media print{a.delete,a.edit,button,input,select,#searchInput{display:none!important;}}
</style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark">
  <div class="container-fluid">
    <a class="navbar-brand d-flex align-items-center" href="#"><img src="../img/logorsud.png" style="height:40px;margin-right:10px;"></a>
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

<div class="container my-4">
  <div class="card p-4">
    <h1 class="mb-4">📚 Kelola Alat Tulis Kantor (Batch)</h1>

    <div class="p-3 mb-4 bg-light rounded">
      <h5>➕ Tambah Alat Tulis</h5>
      <form method="POST" class="row g-3">
        <div class="col-md-3"><input type="text" name="nama" class="form-control" placeholder="Nama" required></div>
        <div class="col-md-2"><input type="text" name="kode" class="form-control" placeholder="Kode" required></div>
        <div class="col-md-2">
          <select name="kondisi" class="form-select" required>
            <option value="">Kondisi</option><option>Baru</option><option>Sedang</option><option>Rusak</option>
          </select>
        </div>
        <div class="col-md-2"><input type="number" name="tahun" class="form-control" placeholder="Tahun" required></div>
        <div class="col-md-2"><input type="number" name="jumlah" class="form-control" placeholder="Jumlah" required></div>
        <div class="col-md-1 d-grid"><button name="add" class="btn btn-primary">Tambah</button></div>
      </form>
    </div>

    <div class="d-flex justify-content-between mb-3">
      <h5>📑 Daftar ATK</h5>
      <div>
        <button id="buatSuratBtn" class="btn btn-success">📄 Buat Surat Permintaan</button>
        <button class="btn btn-info" data-bs-toggle="collapse" data-bs-target="#historiCollapse">📜 Histori</button>
      </div>
    </div>

    <input id="searchInput" class="form-control mb-3" placeholder="🔍 Cari ATK...">

    <div class="table-responsive">
      <table class="table table-hover text-center align-middle" id="atkTable">
        <thead><tr><th><input type="checkbox" id="checkAll"></th><th>No</th><th>Nama</th><th>Kode</th><th>Kondisi</th><th>Tahun</th><th>Jumlah</th><th>Aksi</th></tr></thead>
        <tbody>
        <?php $no=1;$total=0;while($r=$result->fetch_assoc()):$total+=$r['jumlah']; ?>
          <tr data-id="<?= $r['id'] ?>"><td><input type="checkbox" class="chk-item"></td><td><?= $no++ ?></td>
          <td><?= htmlspecialchars($r['nama']) ?></td><td><?= htmlspecialchars($r['kode']) ?></td>
          <td><span class="badge <?= $r['kondisi']=='Baru'?'bg-success':($r['kondisi']=='Sedang'?'bg-warning text-dark':'bg-danger') ?>"><?= $r['kondisi'] ?></span></td>
          <td><?= $r['tahun'] ?></td>
          <td><input type="number" class="form-control jumlah-item" value="<?= $r['jumlah'] ?>" readonly></td>
          <td><a class="btn btn-sm btn-warning edit" data-bs-toggle="modal" data-bs-target="#editModal"
                 data-id="<?= $r['id'] ?>" data-nama="<?= htmlspecialchars($r['nama']) ?>"
                 data-kode="<?= htmlspecialchars($r['kode']) ?>" data-kondisi="<?= htmlspecialchars($r['kondisi']) ?>"
                 data-tahun="<?= $r['tahun'] ?>" data-jumlah="<?= $r['jumlah'] ?>">Edit</a>
              <a class="btn btn-sm btn-danger" href="?delete=<?= $r['id'] ?>" onclick="return confirm('Hapus?')">Hapus</a></td></tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
    <p class="text-end fw-bold mt-2 text-secondary">Total ATK: <?= $total ?></p>

    <div class="collapse" id="historiCollapse">
      <div class="card card-body mt-3 table-responsive">
        <table class="table table-striped text-center align-middle" id="historiTable">
          <thead><tr><th>No</th><th>Nama</th><th>Instalasi</th><th>Pemohon</th><th>Permintaan</th><th>Realisasi</th><th>Tanggal</th></tr></thead>
          <tbody><?php $n=1;while($h=$histori->fetch_assoc()): ?><tr>
            <td><?= $n++ ?></td><td><?= htmlspecialchars($h['nama_atk']) ?></td>
            <td><?= htmlspecialchars($h['instalasi']) ?></td><td><?= htmlspecialchars($h['pemohon']) ?></td>
            <td><?= $h['jumlah_permintaan'] ?></td><td><?= $h['jumlah_realisasi'] ?></td>
            <td><?= date('d-m-Y H:i',strtotime($h['tanggal'])) ?></td></tr><?php endwhile; ?></tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="editModal"><div class="modal-dialog"><div class="modal-content">
<form method="POST"><div class="modal-header"><h5>Edit ATK</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body row g-3">
<input type="hidden" name="id" id="edit-id">
<div class="col-md-6"><input name="nama" id="edit-nama" class="form-control" required></div>
<div class="col-md-6"><input name="kode" id="edit-kode" class="form-control" required></div>
<div class="col-md-6"><select name="kondisi" id="edit-kondisi" class="form-select"><option>Baru</option><option>Sedang</option><option>Rusak</option></select></div>
<div class="col-md-6"><input name="tahun" id="edit-tahun" type="number" class="form-control"></div>
<div class="col-md-6"><input name="jumlah" id="edit-jumlah" type="number" class="form-control"></div></div>
<div class="modal-footer"><button name="edit" class="btn btn-primary">Simpan</button></div></form></div></div></div>

<!-- Modal Batch -->
<div class="modal fade" id="instalasiModal"><div class="modal-dialog modal-xl"><div class="modal-content">
<form id="instalasiForm"><div class="modal-header"><h5>📝 Data Permintaan Batch</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body"><div class="row mb-3">
<div class="col-md-6"><label>Instalasi</label><input id="instalasiInputModal" class="form-control" required></div>
<div class="col-md-6"><label>Pemohon</label><input id="pemohonInputModal" class="form-control" required></div></div>
<div class="table-responsive"><table class="table table-bordered" id="batchTable"><thead><tr><th>Nama</th><th>Stok</th><th>Permintaan</th><th>Realisasi</th></tr></thead><tbody></tbody></table></div></div>
<div class="modal-footer"><button class="btn btn-success" type="submit">💾 Submit Batch</button></div></form></div></div></div>

<footer>© <?= date('Y') ?> RSUD Bedas Tegalluar</footer>

<script>
// Edit modal
document.getElementById('editModal').addEventListener('show.bs.modal',e=>{
 let b=e.relatedTarget;['id','nama','kode','kondisi','tahun','jumlah'].forEach(f=>{document.getElementById('edit-'+f).value=b.dataset[f];});
});

// Search
document.getElementById('searchInput').addEventListener('input',e=>{
 let v=e.target.value.toLowerCase();
 document.querySelectorAll('#atkTable tbody tr').forEach(r=>r.style.display=r.children[2].textContent.toLowerCase().includes(v)?'':'none');
});

// Check all
document.getElementById('checkAll').addEventListener('change',e=>{
 document.querySelectorAll('.chk-item').forEach(c=>c.checked=e.target.checked);
});

// Buat Surat
document.getElementById('buatSuratBtn').onclick=()=>{
 const rows=[...document.querySelectorAll('#atkTable tbody tr')].filter(r=>r.querySelector('.chk-item').checked);
 if(!rows.length) return alert('Pilih minimal satu item!');
 const tb=document.querySelector('#batchTable tbody');tb.innerHTML='';
 rows.forEach(r=>{
  const nama=r.children[2].textContent,stok=r.querySelector('.jumlah-item').value;
  tb.innerHTML+=`<tr data-id="${r.dataset.id}"><td>${nama}</td><td>${stok}</td>
  <td><input type="number" class="form-control permintaan" min="0" max="${stok}" value="0"></td>
  <td><input type="number" class="form-control realisasi" min="0" max="${stok}" value="0"></td></tr>`;
 });
 new bootstrap.Modal('#instalasiModal').show();
};

// Submit batch
document.getElementById('instalasiForm').addEventListener('submit',async e=>{
 e.preventDefault();
 const instalasi=instalasiInputModal.value.trim(),pemohon=pemohonInputModal.value.trim();
 if(!instalasi||!pemohon)return alert('Lengkapi data!');
 const items=[...document.querySelectorAll('#batchTable tbody tr')].map(tr=>({
  id:tr.dataset.id,permintaan:+tr.querySelector('.permintaan').value,realisasi:+tr.querySelector('.realisasi').value,instalasi,pemohon
 }));
 const fd=new FormData();fd.append('batch_items',JSON.stringify(items));
 const res=await fetch('<?=basename(__FILE__)?>',{method:'POST',body:fd});
 const data=await res.json();
 if(!data.success)return alert(data.msg);
 items.forEach(it=>{
  const row=document.querySelector(`#atkTable tr[data-id='${it.id}']`);
  row.querySelector('.jumlah-item').value-=it.realisasi;
 });
 let last=document.querySelector('#historiTable tbody').rows.length;
 data.histori.forEach(h=>{
  const tr=document.createElement('tr');
  tr.innerHTML=`<td>${++last}</td><td>${h.nama_atk}</td><td>${h.instalasi}</td><td>${h.pemohon}</td><td>${h.jumlah_permintaan}</td><td>${h.jumlah_realisasi}</td><td>${new Date(h.tanggal).toLocaleString('id-ID')}</td>`;
  document.querySelector('#historiTable tbody').prepend(tr);
 });
 generatePDF(instalasi,pemohon,items);
 alert('PDF berhasil dibuat!');
 bootstrap.Modal.getInstance(document.getElementById('instalasiModal')).hide();
});

// Generate PDF
function generatePDF(instalasi, pemohon, items) {
  const now = new Date();
  const fileName = `Surat_Permintaan_ATK_${now.getFullYear()}${String(now.getMonth()+1).padStart(2,'0')}${String(now.getDate()).padStart(2,'0')}_${String(now.getHours()).padStart(2,'0')}${String(now.getMinutes()).padStart(2,'0')}${String(now.getSeconds()).padStart(2,'0')}.pdf`;

  const el = document.createElement('div');

  // Buat baris tabel ATK
  const rows = items.map((it, i) => {
    const row = document.querySelector(`#atkTable tr[data-id='${it.id}']`);
    const namaBarang = row ? row.querySelector("td:nth-child(3)").textContent : "(Tidak ditemukan)";
    const ket = it.ket ?? "";

    return `
      <tr>
        <td style="text-align:center;">${i + 1}</td>
        <td>${namaBarang}</td>
        <td style="text-align:center;">${it.permintaan}</td>
        <td style="text-align:center;">${it.realisasi}</td>
        <td>${ket}</td>
      </tr>
    `;
  }).join("");

  // HTML utama untuk PDF
  el.innerHTML = `
  <div style="font-family:'Times New Roman'; padding:25px; font-size:14px; line-height:1.4;">

    <!-- HEADER -->
    <table width="100%" style="border-bottom:3px solid black; padding-bottom:10px; margin-bottom:15px;">
      <tr>
        <td width="110px" style="text-align:left;">
          <img src="../img/logorsud.png" style="width:100px;">
        </td>
        <td style="text-align:center;"> 
          <div style="font-size:18px; font-weight:bold; line-height:1.2;">
            RUMAH SAKIT UMUM DAERAH<br>
            BEDAS TEGALLUAR
          </div>
          <div style="font-size:12px; margin-top:3px; line-height:1.3;">
            Jln. Rancawangi Desa Tegalluar, Kec. Bojongsoang, Kab. Bandung, 40287<br>
            Hotline: 081298569588 | Email: rsudbedas.tegalluar@gmail.com
          </div>
        </td>
      </tr>
    </table>

    <!-- JUDUL -->
    <h3 style="text-align:center; margin-top:5px; text-decoration:underline; font-size:18px;">
      SURAT PERMINTAAN ALAT TULIS KANTOR (ATK)
    </h3>

    <!-- INFORMASI -->
    <table style="margin-top:20px; font-size:14px;">
      <tr>
        <td width="120px">Tanggal</td>
        <td>: ${now.toLocaleDateString('id-ID')}</td>
      </tr>
      <tr>
        <td>Instalasi</td>
        <td>: <b>${instalasi}</b></td>
      </tr>
      <tr>
        <td>Pemohon</td>
        <td>: <b>${pemohon}</b></td>
      </tr>
    </table>

    <!-- TABEL ATK -->
    <table border="1" cellspacing="0" cellpadding="6" width="100%" 
           style="border-collapse:collapse; margin-top:20px; font-size:13px;">
      <thead>
        <tr style="background:#f0f0f0; text-align:center; font-weight:bold;">
          <th width="40px">No</th>
          <th>Nama Barang</th>
          <th width="90px">Permintaan</th>
          <th width="90px">Realisasi</th>
          <th width="120px">Keterangan</th>
        </tr>
      </thead>
      <tbody>
        ${rows}
      </tbody>
    </table>

    <!-- TANDA TANGAN -->
    <div style="margin-top:60px; text-align:right; font-size:14px;">
      Mengetahui,<br>
      <b>Kepala Instalasi</b><br><br><br><br>
      (....................................)
    </div>

  </div>
  `;

  // Generate PDF menggunakan html2pdf
  html2pdf()
    .set({
      margin: 10,
      filename: fileName,
      html2canvas: { scale: 2 },
      jsPDF: { orientation: 'portrait', unit: 'mm', format: 'a4' }
    })
    .from(el)
    .save();
}


</script>
</body>
</html>
