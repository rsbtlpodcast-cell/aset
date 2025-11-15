<?php
require_once __DIR__ . '/../inc/auth.php';
require_login();
require_once __DIR__ . '/../inc/db.php';

if (isset($_POST['submit'])) {
    $nama_pemohon = $_POST['nama_pemohon'];
    $instalasi    = $_POST['instalasi'];
    $nama_barang  = $_POST['nama_barang'];
    $jumlah       = $_POST['jumlah'];
    $keterangan   = $_POST['keterangan'];

    $stmt = $db->prepare("INSERT INTO permintaan_atk (nama_pemohon, instalasi, nama_barang, jumlah, keterangan, status, created_at) 
                          VALUES (?, ?, ?, ?, ?, 'pending', NOW())");
    $stmt->bind_param("sssis", $nama_pemohon, $instalasi, $nama_barang, $jumlah, $keterangan);

    if ($stmt->execute()) {
        echo "<script>alert('Permintaan berhasil dikirim!'); window.location='user.php';</script>";
    } else {
        echo "<script>alert('Gagal mengirim permintaan!'); window.location='user.php';</script>";
    }
}
