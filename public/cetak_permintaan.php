<?php
require_once __DIR__ . '/../inc/auth.php';
require_login();
require_once __DIR__ . '/../inc/db.php';

$user = $_SESSION['user'];

// Ambil semua ATK terbaru
$result = $conn->query("SELECT * FROM alat_tulis ORDER BY nama ASC");

// Format tanggal Indonesia
function tgl_indo($tanggal) {
    $bulan = ["Januari","Februari","Maret","April","Mei","Juni",
              "Juli","Agustus","September","Oktober","November","Desember"];
    $pecah = explode('-', $tanggal);
    return $pecah[2]." ".$bulan[(int)$pecah[1]-1]." ".$pecah[0];
}

// Nomor surat otomatis (misal: 001/ATK/RSBTL/IX/2025)
$no_surat = "001/ATK/RSBTL/".date("m")."/".date("Y");
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Laporan ATK - RSUD Bedas Tegalluar</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/html2pdf.js@0.10.1/dist/html2pdf.bundle.min.js"></script>
<style>
body { font-family: 'Times New Roman', serif; font-size: 12pt; }
.header { text-align:center; margin-bottom:10px; }
.header img { height:80px; }
.header hr { border-top:2px solid #000; margin:3px 0; }
.header hr.sub { border-top:1px solid #000; margin:2px 0; }

h3 { text-align:center; text-decoration:underline; margin:15px 0; }

table { width:100%; border-collapse:collapse; margin-top:15px; }
table th, table td { border:1px solid #000; padding:8px; text-align:center; font-size:12pt; }
table th { background:#f5f5f5; font-weight:bold; }

.signature { width:100%; margin-top:50px; text-align:center; }
.signature td { height:100px; vertical-align:bottom; }

@media print {
    button { display:none; }
}
</style>
</head>
<body>
<div id="laporan">
  <!-- Kop Surat -->
  <div class="header">
    <img src="../img/logorsud.png" alt="Logo RSUD">
    <div style="font-size:18pt; font-weight:bold;">RSUD BEDAS TEGALLUAR</div>
    <div style="font-size:12pt;">Jl. Raya Tegalluar No. 123, Bandung</div>
    <div style="font-size:12pt;">Telp: (022) 1234567 | Email: info@rsudbedas.go.id</div>
    <hr>
    <hr class="sub">
  </div>

  <!-- Judul Surat -->
  <h3>SURAT PERMINTAAN BARANG</h3>
  <p style="text-align:center;">Nomor: <?= $no_surat ?><br>
  Tanggal: <?= tgl_indo(date("Y-m-d")) ?></p>

  <p>Kepada Yth,<br>Bagian Logistik / ATK<br>RSUD Bedas Tegalluar</p>
  <p>Dengan hormat, bersama ini kami mengajukan permintaan barang sebagai berikut:</p>

  <!-- Tabel Barang -->
  <table>
    <thead>
      <tr>
        <th>No</th>
        <th>Nama Barang</th>
        <th>Jumlah</th>
        <th>Keterangan</th>
        <th>Paraf Penerima</th>
      </tr>
    </thead>
    <tbody>
      <?php $no=1; while($row=$result->fetch_assoc()): ?>
      <tr>
        <td><?= $no++ ?></td>
        <td><?= htmlspecialchars($row['nama']) ?></td>
        <td><?= $row['jumlah'] ?></td>
        <td><?= htmlspecialchars($row['kondisi']) ?></td>
        <td></td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>

  <p>Demikian Surat Permintaan ini kami sampaikan, atas perhatiannya diucapkan terima kasih.</p>

  <!-- Tanda tangan -->
  <table class="signature">
    <tr>
      <td>Pemohon,<br><br><br><br>(............................)</td>
      <td>Mengetahui,<br>Kepala Bagian<br><br><br>(............................)</td>
    </tr>
  </table>
</div>

<!-- Tombol Cetak / PDF -->
<div style="text-align:center; margin-top:20px;">
  <button onclick="cetakLaporan()">🖨 Cetak / Download PDF</button>
</div>

<script>
function cetakLaporan() {
  const element = document.getElementById("laporan");
  html2pdf().set({
    margin:10,
    filename:'surat_permintaan_atk.pdf',
    image:{type:'jpeg', quality:1},
    html2canvas:{scale:2},
    jsPDF:{unit:'mm', format:'a4', orientation:'landscape'}
  }).from(element).save();
}
</script>
</body>
</html>
