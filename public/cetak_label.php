<?php
require_once __DIR__ . '/../inc/auth.php';
require_login();
require_once __DIR__ . '/../inc/db.php';

if (!isset($_GET['id'])) die("ID aset tidak ditemukan.");

$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM aset WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$aset = $result->fetch_assoc();
if (!$aset) die("Data aset tidak ditemukan.");

// ==== Buat kanvas ====
$width = 900;
$height = 400;
$img = imagecreatetruecolor($width, $height);

// ==== Warna ====
$white = imagecolorallocate($img, 255, 255, 255);
$black = imagecolorallocate($img, 0, 0, 0);
$gray  = imagecolorallocate($img, 230, 230, 230);

imagefill($img, 0, 0, $white);
imagerectangle($img, 0, 0, $width-1, $height-1, $black);

// ==== Area logo ====
$logo_path = __DIR__ . '/../img/logorsud2.png';
$logo_box_width = 320;
imagerectangle($img, 0, 0, $logo_box_width, $height-1, $black);

if (file_exists($logo_path)) {
    $logo = imagecreatefrompng($logo_path);
    $logo_w = imagesx($logo);
    $logo_h = imagesy($logo);
    $new_w = 250;
    $new_h = ($logo_h / $logo_w) * $new_w;
    $x = 35;
    $y = ($height / 2) - ($new_h / 2);
    imagecopyresampled($img, $logo, $x, $y, 0, 0, $new_w, $new_h, $logo_w, $logo_h);
    imagedestroy($logo);
}

// ==== Data Aset ====
$x_start = $logo_box_width + 1;
$row_height = 65;
$col1_width = 270;

$fields = [
    "Nama Barang" => $aset['nama_barang'],
    "Merk / Tipe" => $aset['merek'],
    "No Registrasi" => $aset['no_registrasi'],
    "No Seri" => $aset['no_seri'],
    "Tahun" => $aset['tahun_pengadaan'],
    "Ruangan" => $aset['lokasi']
];

$y = 0;
foreach ($fields as $label => $value) {
    imagerectangle($img, $x_start, $y, $width-1, $y+$row_height, $black);
    imageline($img, $x_start+$col1_width, $y, $x_start+$col1_width, $y+$row_height, $black);
    imagestring($img, 5, $x_start+10, $y+20, $label, $black);
    imagestring($img, 5, $x_start+$col1_width+15, $y+20, ": " . $value, $black);
    $y += $row_height;
}

// ==== Output file ====
$filename = "label_aset_" . preg_replace('/[^a-zA-Z0-9]/', '_', $aset['nama_barang']) . ".jpg";
header('Content-Type: image/jpeg');
header('Content-Disposition: attachment; filename="'.$filename.'"');
imagejpeg($img, null, 95);
imagedestroy($img);
exit;
?>
