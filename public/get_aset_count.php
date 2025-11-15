<?php
require_once __DIR__ . '/../inc/auth.php'; 
require_login();
require_once __DIR__ . '/../inc/db.php';

// Ambil jumlah aset
$asetKesehatan = intval($conn->query("SELECT COUNT(*) AS total FROM aset_kesehatan")->fetch_assoc()['total']);
$asetNonKesehatan = intval($conn->query("SELECT COUNT(*) AS total FROM aset_non_kesehatan")->fetch_assoc()['total']);

// Kembalikan JSON
header('Content-Type: application/json');
echo json_encode([
    'asetKesehatan' => $asetKesehatan,
    'asetNonKesehatan' => $asetNonKesehatan
]);
