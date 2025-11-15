<?php
require_once __DIR__ . '/../inc/db.php';

$data = json_decode($_POST['items'], true);
if(!$data){ echo json_encode(['success'=>false,'msg'=>'Data kosong']); exit; }

foreach($data as $it){
    $id = (int)$it['id'];
    $realisasi = (int)$it['realisasi'];

    $stmt = $conn->prepare("UPDATE alat_tulis SET jumlah = jumlah - ? WHERE id=? AND jumlah >= ?");
    $stmt->bind_param("iii", $realisasi, $id, $realisasi);
    $stmt->execute();
}
echo json_encode(['success'=>true]);
