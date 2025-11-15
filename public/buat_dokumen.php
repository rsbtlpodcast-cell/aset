<?php
require_once __DIR__ . '/../inc/auth.php';
require_login();
$user = $_SESSION['user'];
require_once __DIR__ . '/../inc/db.php';

// Untuk highlight menu aktif
$current_page = basename($_SERVER['PHP_SELF']);

$message = "";
$tipe = $_POST['tipe'] ?? "";

// Map folder upload
$folderMap = [
    "serah_terima" => "serah_terima",
    "uji_fungsi"   => "uji_fungsi",
    "training"     => "training",
    "instalasi"    => "instalasi",
    "kalibrasi"    => "kalibrasi"
];

// === Cek peringatan kalibrasi ===
$notifKalibrasi = [];
$q = $conn->query("SELECT nama_file, tanggal_kalibrasi_berikutnya FROM kalibrasi_log WHERE status IN ('aktif','peringatan')");
while ($row = $q->fetch_assoc()) {
    $selisih = (strtotime($row['tanggal_kalibrasi_berikutnya']) - time()) / (60*60*24);

    if ($selisih <= 30 && $selisih > 0) {
        $notifKalibrasi[] = "⚠️ Dokumen kalibrasi <b>{$row['nama_file']}</b> akan kedaluwarsa dalam <b>" . ceil($selisih) . " hari</b>. Harap diperbarui.";
        $conn->query("UPDATE kalibrasi_log SET status='peringatan' WHERE nama_file='".$row['nama_file']."'");

        // === Kirim notifikasi email otomatis (sekali saja per file) ===
        $cekNotif = $conn->query("SELECT notifikasi_terkirim FROM kalibrasi_log WHERE nama_file='".$row['nama_file']."'")->fetch_assoc();
        if ($cekNotif && $cekNotif['notifikasi_terkirim'] == 0) {
            $to = "admin@rs-bedas-tegalluar.go.id"; // ubah ke email admin kamu
            $subject = "⚠️ Peringatan Kalibrasi Akan Kedaluwarsa";
            $messageEmail = "
            <html>
            <body style='font-family:Segoe UI,sans-serif;'>
            <h3 style='color:#d9534f;'>Peringatan Kalibrasi Akan Kedaluwarsa</h3>
            <p>Dokumen kalibrasi berikut akan kedaluwarsa dalam <b>" . ceil($selisih) . " hari</b>:</p>
            <ul>
              <li><b>Nama File:</b> {$row['nama_file']}</li>
              <li><b>Tanggal Kalibrasi Berikutnya:</b> {$row['tanggal_kalibrasi_berikutnya']}</li>
            </ul>
            <p>Segera lakukan proses kalibrasi ulang agar status tetap aktif.</p>
            <hr>
            <small>Email ini dikirim otomatis oleh sistem SIAT RSUD Bedas Tegalluar.</small>
            </body>
            </html>";

            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= "From: SIAT RSUD Bedas Tegalluar <no-reply@rs-bedas-tegalluar.go.id>" . "\r\n";

            if (mail($to, $subject, $messageEmail, $headers)) {
                $conn->query("UPDATE kalibrasi_log SET notifikasi_terkirim=1 WHERE nama_file='".$row['nama_file']."'");
            }
        }

    } elseif ($selisih <= 0) {
        $conn->query("UPDATE kalibrasi_log SET status='kedaluwarsa' WHERE nama_file='".$row['nama_file']."'");
    }
}

// === Proses Upload ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $tipe = $_POST['tipe'] ?? "";
    $folder = $folderMap[$tipe] ?? "";
    if ($folder) {
        $targetDir = __DIR__ . "/../uploads/" . $folder . "/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

        $fileName = time() . "_" . basename($_FILES["file"]["name"]);
        $targetFile = $targetDir . $fileName;
        $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

        $allowedTypes = ["pdf", "jpg", "jpeg", "png", "doc", "docx"];
        if (in_array($fileType, $allowedTypes)) {
            if ($_FILES["file"]["size"] > 10*1024*1024) { // Maksimal 10 MB
                $message = "❌ Ukuran file maksimal 10MB.";
            } elseif (move_uploaded_file($_FILES["file"]["tmp_name"], $targetFile)) {
                $message = "✅ File berhasil diupload ke folder <b>" . ucfirst(str_replace("_", " ", $folder)) . "</b>: " . htmlspecialchars($fileName);

                // Simpan log kalibrasi jika tipe kalibrasi
                if ($tipe === 'kalibrasi') {
                    $tgl_upload = date('Y-m-d');
                    $tgl_berikutnya = date('Y-m-d', strtotime('+1 year', strtotime($tgl_upload))); // masa kalibrasi 1 tahun
                    $status = 'aktif';
                    $stmt = $conn->prepare("INSERT INTO kalibrasi_log (nama_file, tanggal_upload, tanggal_kalibrasi_berikutnya, status, notifikasi_terkirim) VALUES (?,?,?,?,0)");
                    $stmt->bind_param('ssss', $fileName, $tgl_upload, $tgl_berikutnya, $status);
                    $stmt->execute();
                }

            } else {
                $message = "❌ Gagal upload file.";
            }
        } else {
            $message = "❌ Format file tidak diizinkan.";
        }
    } else {
        $message = "❌ Pilih kategori dokumen terlebih dahulu.";
    }
}

// === Proses Hapus ===
if (isset($_GET['delete'], $_GET['tipe'])) {
    $tipe = $_GET['tipe'];
    $folder = $folderMap[$tipe] ?? "";
    $file = basename($_GET['delete']);
    $targetFile = __DIR__ . "/../uploads/$folder/$file";
    if ($folder && file_exists($targetFile)) {
        unlink($targetFile);
        $message = "🗑 File berhasil dihapus.";
        if ($tipe === 'kalibrasi') {
            $conn->query("DELETE FROM kalibrasi_log WHERE nama_file='$file'");
        }
    } else {
        $message = "❌ File tidak ditemukan.";
    }
}

// === Proses Rename ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rename_old'], $_POST['rename_new'], $_POST['tipe'])) {
    $tipe = $_POST['tipe'];
    $folder = $folderMap[$tipe] ?? "";
    $old = basename($_POST['rename_old']);
    $new = preg_replace("/[^a-zA-Z0-9_\-.]/", "_", $_POST['rename_new']);
    $dir = __DIR__ . "/../uploads/$folder/";
    if ($folder && file_exists($dir . $old)) {
        if (rename($dir . $old, $dir . $new)) {
            $message = "✏️ File berhasil diganti nama menjadi: $new";
            if ($tipe === 'kalibrasi') {
                $conn->query("UPDATE kalibrasi_log SET nama_file='$new' WHERE nama_file='$old'");
            }
        } else {
            $message = "❌ Gagal mengganti nama file.";
        }
    } else {
        $message = "❌ File lama tidak ditemukan.";
    }
}

// === Ambil daftar file ===
function listFiles($folderName)
{
    $dir = __DIR__ . "/../uploads/" . $folderName . "/";
    if (!is_dir($dir)) return [];
    return array_diff(scandir($dir), ['.', '..']);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Buat Dokumen BA - SIAT RSUD Bedas Tegalluar</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
body { font-family:'Segoe UI',sans-serif; background:#f3f6fa; display:flex; flex-direction:column; min-height:100vh; margin:0; }
.container { flex:1 0 auto; }
.navbar { background:#273c75; box-shadow:0 3px 10px rgba(0,0,0,0.15); }
.navbar-brand, .nav-link { color:white !important; font-weight:500; transition:0.3s; }
.nav-link:hover { color:#fbc531 !important; }
.nav-link.active { color:#273c75 !important; background-color:#fbc531 !important; border-radius:6px; font-weight:600; }
.navbar-brand img { height:40px; margin-right:10px; }
.card { border-radius:20px; box-shadow:0 10px 25px rgba(0,0,0,0.08); }
h1 { color:#273c75; font-weight:700; }
footer { flex-shrink:0; padding:15px 0; text-align:center; background:#f1f1f1; color:#555; margin-top:auto; }
.tab-btn.active { background:#fbc531 !important; color:#273c75 !important; border-radius:8px; }
.btn-icon { border:none; background:none; cursor:pointer; }
.btn-icon.delete { color:#dc3545; }
.btn-icon.edit { color:#0d6efd; }
.btn-icon:hover { transform:scale(1.2); transition:0.2s; }
</style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark">
  <div class="container-fluid">
    <a class="navbar-brand d-flex align-items-center" href="#">
      <img src="../img/logorsud.png" alt="Logo RSUD"> 
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link <?= $current_page=='index.php'?'active':'' ?>" href="index.php"><i class="fas fa-home"></i> Dashboard</a></li>
        <li class="nav-item"><a class="nav-link <?= $current_page=='aset.php'?'active':'' ?>" href="aset.php"><i class="fas fa-boxes"></i> Kelola Aset</a></li>
        <li class="nav-item"><a class="nav-link <?= $current_page=='kategori.php'?'active':'' ?>" href="kategori.php"><i class="fas fa-file-alt"></i> Laporan Aset</a></li>
        <li class="nav-item"><a class="nav-link <?= $current_page=='aset_kesehatan.php'?'active':'' ?>" href="aset_kesehatan.php"><i class="fas fa-pills"></i> Alkes</a></li>
        <li class="nav-item"><a class="nav-link <?= $current_page=='aset_non_kesehatan.php'?'active':'' ?>" href="aset_non_kesehatan.php"><i class="fas fa-tools"></i> Non-Alkes</a></li>
        <li class="nav-item"><a class="nav-link <?= $current_page=='alat_tulis.php'?'active':'' ?>" href="alat_tulis.php"><i class="fas fa-hand-holding"></i> Permintaan</a></li>
        <li class="nav-item"><a class="nav-link <?= $current_page=='buat_dokumen.php'?'active':'' ?>" href="buat_dokumen.php"><i class="fas fa-file-contract"></i> Buat Dokumen BA</a></li>
        <li class="nav-item"><a class="nav-link text-danger <?= $current_page=='logout.php'?'active':'' ?>" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
      </ul>
    </div>
  </div>
</nav>

<!-- Konten -->
<div class="container my-4">
  <div class="card p-4">
    <h1 class="text-center mb-3">📑 Buat Dokumen Berita Acara</h1>
    <p class="text-center">Halo, <b><?=htmlspecialchars($user['username'])?></b>. Silakan upload dokumen Berita Acara sesuai kategori di bawah.</p>

    <?php if (!empty($notifKalibrasi)): ?>
      <div class="alert alert-warning fw-bold"><?= implode('<br>', $notifKalibrasi) ?></div>
    <?php endif; ?>

    <?php if ($message): ?>
      <div class="alert <?= strpos($message,'✅')!==false || strpos($message,'🗑')!==false || strpos($message,'✏️')!==false ? 'alert-success' : 'alert-danger' ?> text-center fw-bold"><?=$message?></div>
    <?php endif; ?>

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-3 justify-content-center flex-wrap">
      <?php foreach ($folderMap as $key => $val): ?>
        <li class="nav-item"><button class="nav-link tab-btn <?= $key=='serah_terima'?'active':'' ?>" data-bs-toggle="tab" data-bs-target="#<?=$key?>"><?= ucfirst(str_replace('_',' ',$key)) ?></button></li>
      <?php endforeach; ?>
    </ul>

    <div class="tab-content">
      <?php foreach ($folderMap as $key => $folder): ?>
      <div class="tab-pane fade <?= $key=='serah_terima'?'show active':'' ?>" id="<?=$key?>">
        <form method="post" enctype="multipart/form-data" class="mb-3">
          <input type="hidden" name="tipe" value="<?=$key?>">
          <input type="file" name="file" required>
          <button type="submit" class="btn btn-primary btn-sm">⬆ Upload</button>
        </form>

        <table class="table table-bordered align-middle text-center">
          <thead class="table-light">
            <tr>
              <th>Nama File</th>
              <?php if ($key === 'kalibrasi'): ?><th>Status Kalibrasi</th><?php endif; ?>
              <th width="180">Aksi</th>
            </tr>
          </thead>
          <tbody>
          <?php
          foreach (listFiles($folder) as $f):
            echo "<tr>";
            echo "<td><a href=\"../uploads/$folder/$f\" target=\"_blank\" class=\"text-decoration-none\">📄 $f</a></td>";

            if ($key === 'kalibrasi') {
                $info = $conn->query("SELECT tanggal_kalibrasi_berikutnya, status FROM kalibrasi_log WHERE nama_file='$f'")->fetch_assoc();
                if ($info) {
                    $tgl_berikut = strtotime($info['tanggal_kalibrasi_berikutnya']);
                    $sisa = ceil(($tgl_berikut - time()) / (60*60*24));
                    if ($sisa > 30) {
                        $statusKal = "<span class='badge bg-success'>Aktif</span> <br><small>Masih $sisa hari lagi</small>";
                    } elseif ($sisa > 0) {
                        $statusKal = "<span class='badge bg-warning text-dark'>Mendekati</span> <br><small>Sisa $sisa hari</small>";
                    } else {
                        $statusKal = "<span class='badge bg-danger'>Kedaluwarsa</span> <br><small>".abs($sisa)." hari lewat</small>";
                    }
                } else {
                    $statusKal = "<span class='badge bg-secondary'>Tidak Ada Data</span>";
                }
                echo "<td>$statusKal</td>";
            }
          ?>
            <td>
              <a href="?delete=<?=$f?>&tipe=<?=$key?>" onclick="return confirm('Hapus file ini?')" class="btn-icon delete" title="Hapus"><i class="fas fa-trash"></i></a>
              <form method="post" class="d-inline">
                <input type="hidden" name="tipe" value="<?=$key?>">
                <input type="hidden" name="rename_old" value="<?=$f?>">
                <input type="text" name="rename_new" value="<?=$f?>" size="15" class="form-control d-inline w-auto">
                <button type="submit" class="btn-icon edit" title="Rename"><i class="fas fa-pen"></i></button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<footer>© <?=date('Y')?> RSUD Bedas Tegalluar - Sistem Aset Terpadu (SIAT)</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Tambahan agar tab tetap aktif setelah reload -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  const tabButtons = document.querySelectorAll('.tab-btn');
  const savedTab = localStorage.getItem('activeTab');

  if (savedTab) {
    const tabTrigger = document.querySelector(`[data-bs-target="${savedTab}"]`);
    const tabContent = document.querySelector(savedTab);
    if (tabTrigger && tabContent) {
      tabButtons.forEach(btn => btn.classList.remove('active'));
      document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('show', 'active'));
      tabTrigger.classList.add('active');
      tabContent.classList.add('show', 'active');
    }
  }

  tabButtons.forEach(btn => {
    btn.addEventListener('click', () => {
      const tabId = btn.getAttribute('data-bs-target');
      localStorage.setItem('activeTab', tabId);
    });
  });
});
</script>
</body>
</html>
