<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Laporan Barang Aset</title>
<style>
body { font-family: "Times New Roman", serif; font-size: 12pt; }
.kop { text-align:center; line-height:1.4; margin-bottom:10px; }
.kop hr { border:1px solid #000; margin:6px 0; }
.kop table { width:100%; border:none; }
.kop td { border:none; vertical-align:middle; }
h3 { margin: 12px 0; font-size: 16pt; text-decoration: underline; }

table.laporan { width:100%; border-collapse: collapse; margin-top:15px; }
table.laporan th, table.laporan td { border:1px solid #000; padding:6px; text-align:center; }
table.laporan th { background:#eee; font-size: 11pt; }
table.laporan td { font-size: 11pt; }

.footer { margin-top:40px; width:100%; }
.footer td { text-align:center; vertical-align:top; }

/* Hilangkan header/footer default browser */
@page {
  size: A4;
  margin: 15mm;
}
@media print {
  body::before,
  body::after {
    display: none !important;
    content: none !important;
  }
}
</style>
</head>
<body onload="window.print()">

<div class="kop">
    <table>
        <tr>
            <td style="width:100px; text-align:center;">
                <img src="../img/logokab.png" alt="Logo Kabupaten Bandung" style="width:100px; height:auto;">
            </td>
            <td style="text-align:center;">
                <div><b>PEMERINTAH KABUPATEN BANDUNG</b></div>
                <div><b>DINAS KESEHATAN</b></div>
                <div style="font-size:16px; font-weight:bold;">RUMAH SAKIT UMUM DAERAH BEDAS TEGALLUAR</div>
                <div style="font-size:11pt;">Jln. Rancawangi Desa Tegalluar Kec. Bojongsoang Kode Pos 40287 Kab.Bandung</div>
                <div style="font-size:11pt;">Hotline : 081298569588, Email : rsudbedas.tegalluar@gmail.com</div>
            </td>
        </tr>
    </table>
    <hr>
</div>

<h3 style="text-align:center;">LAPORAN DATA BARANG ASET</h3>

<table class="laporan">
<thead>
<tr>
    <th style="width:40px;">No</th>
    <th>Nama Barang</th>
    <th>Merek</th>
    <th>No Registrasi</th>
    <th>Tahun Pengadaan</th>
    <th>Lokasi</th>
</tr>
</thead>
<tbody>
<?php
require_once __DIR__ . '/../inc/db.php';

// Urutkan berdasarkan lokasi (abjad) lalu nama barang
$result = $conn->query("SELECT * FROM aset ORDER BY lokasi ASC, nama_barang ASC");

$no=1;
while ($row=$result->fetch_assoc()):
?>
<tr>
    <td><?= $no++ ?></td>
    <td style="text-align:left;"><?= htmlspecialchars($row['nama_barang']) ?></td>
    <td><?= htmlspecialchars($row['merek']) ?></td>
    <td><?= htmlspecialchars($row['no_registrasi']) ?></td>
    <td><?= htmlspecialchars($row['tahun_pengadaan']) ?></td>
    <td><?= htmlspecialchars($row['lokasi']) ?></td>
</tr>
<?php endwhile; ?>
</tbody>
</table>

<!-- Footer tanda tangan -->
<table class="footer">
<tr>
    <td style="width:60%;"></td>
    <td>
        Bandung, <?= date('d-m-Y') ?><br>
        Mengetahui,<br><br><br><br>
        <b>(____________________)</b><br>
        
    </td>
</tr>
</table>

</body>
</html>
