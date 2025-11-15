<?php
require_once __DIR__ . '/../inc/db.php';
header('Content-Type: application/json');

$id = $_POST['id'] ?? null;
$nama = $_POST['nama'] ?? '';
$kode = $_POST['kode'] ?? '';
$kondisi = $_POST['kondisi'] ?? '';
$tahun = $_POST['tahun'] ?? '';
$jumlah = $_POST['jumlah'] ?? '';

if(!$id){ echo json_encode(['success'=>false]); exit; }

$stmt = $conn->prepare("UPDATE alat_tulis SET nama=?, kode=?, kondisi=?, tahun=?, jumlah=? WHERE id=?");
$stmt->bind_param("ssssii",$nama,$kode,$kondisi,$tahun,$jumlah,$id);
$stmt->execute();

echo json_encode(['success'=>true]);
