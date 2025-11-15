<?php
require_once __DIR__ . '/../inc/auth.php';
require_login();
require_once __DIR__ . '/../inc/db.php';

// === Ambil data aset dari database ===
$stmt = $conn->prepare("SELECT * FROM kategori ORDER BY nama_barang ASC");
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Review Laporan Aset</title>
<style>
  body {
    font-family: 'Times New Roman', serif;
    margin: 30px;
    background-color: #fff;
    color: #000;
  }
  .kop {
    text-align: center;
    border-bottom: 2px solid black;
    padding-bottom: 10px;
    margin-bottom: 20px;
  }
  .kop img {
    width: 120px;
    float: left;
  }
  .kop h2, .kop h3 {
    margin: 0;
    line-height: 1.3;
  }
  .kop h2 {
    font-size: 16pt;
    font-weight: bold;
  }
  .kop h3 {
    font-size: 16pt; /* <- Disamakan ukurannya */
    font-weight: bold;
  }
  table {
    border-collapse: collapse;
    width: 100%;
    font-size: 12pt;
  }
  th, td {
    border: 1px solid #000;
    padding: 6px;
  }
  th {
    background-color: #f2f2f2;
    text-align: center;
  }
  .tanda-tangan {
    margin-top: 40px;
    text-align: right;
    font-size: 12pt;
  }
  .btn-area {
    text-align: right;
    margin-bottom: 20px;
  }
  button {
    background-color: #007bff;
    color: white;
    border: none;
    padding: 8px 14px;
    border-radius: 5px;
    cursor: pointer;
    font-size: 12pt;
  }
  button:hover {
    background-color: #0056b3;
  }
  /* === Sembunyikan tombol saat dicetak === */
  @media print {
    .btn-area {
      display: none !important;
    }
  }
</style>
</head>
<body>

<div class="btn-area">
  <button onclick="window.print()">🖨️ Cetak / Download PDF</button>
</div>

<div class="kop">
  <img src="../img/logokab.png" alt="Logo Kabupaten">
  <div style="text-align:center;">
    <h2>PEMERINTAH KABUPATEN BANDUNG</h2>
    <h3>DINAS KESEHATAN</h3>
    <h2>RUMAH SAKIT UMUM DAERAH BEDAS TEGALLUAR</h2>
    <p style="font-size:11pt;">
      <br>Jln. Rancawangi Desa Tegalluar Kec. Bojongsoang Kode Pos 40287 Kab. Bandung</br>
      Hotline : 081298569588, Email : rsudbedas.tegalluar@gmail.com
    </p>
  </div>
</div>

<h3 style="text-align:center; margin-bottom:10px;">LAPORAN DATA ASET</h3>

<table>
  <thead>
    <tr>
      <th width="5%">No</th>
      <th width="20%">Nama Barang</th>
      <th width="15%">Merek</th>
      <th width="15%">No Registrasi</th>
      <th width="10%">Tahun</th>
      <th width="15%">Lokasi</th>
      <th width="10%">Jumlah</th>
      <th width="10%">Kategori</th>
    </tr>
  </thead>
  <tbody>
    <?php $no = 1; foreach ($data as $row): ?>
    <tr>
      <td align="center"><?= $no++ ?></td>
      <td><?= htmlspecialchars($row['nama_barang']) ?></td>
      <td><?= htmlspecialchars($row['merek']) ?></td>
      <td><?= htmlspecialchars($row['no_registrasi']) ?></td>
      <td align="center"><?= htmlspecialchars($row['tahun_pengadaan']) ?></td>
      <td><?= htmlspecialchars($row['lokasi']) ?></td>
      <td align="center"><?= htmlspecialchars($row['jumlah']) ?></td>
      <td align="center"><?= htmlspecialchars($row['kategori']) ?></td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<div class="tanda-tangan">
  <p>Bandung, <?= date('d F Y') ?></p>
  <br><br><br>
  <p>(..............................................)</p>
  <p>Kepala RSUD Bedas Tegalluar</p>
</div>

</body>
</html>
