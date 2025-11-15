<?php
require_once __DIR__ . '/../inc/auth.php';
require_login();
require_once __DIR__ . '/../inc/db.php';

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: aset.php");
    exit;
}

// Ambil relasi id_aset_kesehatan
$stmt = $conn->prepare("SELECT id_aset_kesehatan FROM aset WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($id_aset_kesehatan);
$stmt->fetch();
$stmt->close();

if ($id_aset_kesehatan) {
    $conn->begin_transaction();
    try {
        // hapus aset
        $stmt2 = $conn->prepare("DELETE FROM aset WHERE id=?");
        $stmt2->bind_param("i", $id);
        $stmt2->execute();
        $stmt2->close();

        // tambah stok kembali
        $stmt3 = $conn->prepare("UPDATE aset_kesehatan SET jumlah = jumlah + 1 WHERE id=?");
        $stmt3->bind_param("i", $id_aset_kesehatan);
        $stmt3->execute();
        $stmt3->close();

        $conn->commit();
        header("Location: aset.php");
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        die("Error: " . $e->getMessage());
    }
} else {
    header("Location: aset.php");
    exit;
}
