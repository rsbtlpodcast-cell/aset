<?php
require_once __DIR__ . '/../inc/auth.php';
require_login();
require_once __DIR__ . '/../inc/db.php';

$id = intval($_GET['id'] ?? 0);

if ($id > 0) {
    $stmt = $conn->prepare("DELETE FROM kategori WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

header("Location: kategori.php");
exit;
