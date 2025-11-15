<?php
require_once __DIR__ . '/../inc/auth.php'; 
require_login();
require_once __DIR__ . '/../inc/db.php';
$user = $_SESSION['user'];

// Ambil data aset awal untuk diagram (pastikan integer)
$asetKesehatan = intval($conn->query("SELECT COUNT(*) AS total FROM aset_kesehatan")->fetch_assoc()['total']);
$asetNonKesehatan = intval($conn->query("SELECT COUNT(*) AS total FROM aset_non_kesehatan")->fetch_assoc()['total']);
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Dashboard - Aplikasi Aset RSUD</title>
  <link rel="stylesheet" href="../css/style.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: #f5f6fa;
      color: #2f3640;
    }
    nav {
      background: #273c75;
      padding: 12px 25px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      box-shadow: 0 2px 6px rgba(0,0,0,0.2);
      position: sticky;
      top: 0;
      z-index: 1000;
    }
    nav .logo { display: flex; align-items: center; color: white; font-weight: bold; font-size: 20px; }
    nav .logo img { height: 40px; margin-right: 10px; }
    nav .menu { display: flex; gap: 15px; flex-wrap: wrap; }
    nav .menu a {
      color: white; text-decoration: none; font-weight: 500; padding: 8px 12px;
      border-radius: 6px; transition: background 0.3s, color 0.3s;
    }
    nav .menu a:hover { background: #fbc531; color: #273c75; }
    main { padding: 30px; }
    h1 { color: #273c75; font-size: 28px; margin-bottom: 15px; }
    .card { background: white; border-radius: 12px; padding: 25px; box-shadow: 0 6px 20px rgba(0,0,0,0.05); margin-bottom: 25px; }
    .cards-container { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 30px; }
    .cards-container .card .icon { font-size: 36px; margin-bottom: 12px; color: #273c75; }
    .chart-container {
      background: white; border-radius: 12px; padding: 25px; box-shadow: 0 6px 20px rgba(0,0,0,0.05);
      max-width: 400px; margin: 0 auto; height: 400px; position: relative;
    }
    #chartLoading { position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); color:#273c75; font-weight:bold; }
    footer { text-align: center; padding: 15px; background: #273c75; color: white; font-size: 14px; margin-top: 30px; }
    @media (max-width: 768px) { nav { flex-direction: column; align-items: flex-start; } .cards-container { grid-template-columns: 1fr; } }
  </style>
</head>
<body>
  <!-- Navbar -->
  <nav>
    <div class="logo">
      <img src="../img/logorsud.png" alt="Logo RSUD">
      
    </div>
    <div class="menu">
      <a href="index.php"><i class="fas fa-home"></i> Dashboard</a>
      <a href="aset.php"><i class="fas fa-boxes"></i> Kelola Aset</a>
      <a href="kategori.php"><i class="fas fa-file-alt"></i> Laporan Aset</a>
      <a href="aset_kesehatan.php"><i class="fas fa-pills"></i> Alkes</a>
      <a href="aset_non_kesehatan.php"><i class="fas fa-tools"></i> Non-Alkes</a>
      <a href="alat_tulis.php"><i class="fas fa-hand-holding"></i> Permintaan</a>
      <a href="buat_dokumen.php"><i class="fas fa-file-contract"></i> Buat Dokumen BA</a>
      <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
  </nav>

  <!-- Main Content -->
  <main>
    <div class="card">
      <h1>Selamat datang, <?=htmlspecialchars($user['username'])?> (<?=htmlspecialchars($user['role'])?>)</h1>
      <p>Berikut ringkasan aset rumah sakit saat ini:</p>
    </div>

    <div class="cards-container">
      <div class="card">
        <div class="icon"><i class="fas fa-pills"></i></div>
        <h2 id="jumlahKesehatan"><?= $asetKesehatan ?></h2>
        <p>Aset Kesehatan</p>
      </div>
      <div class="card">
        <div class="icon"><i class="fas fa-tools"></i></div>
        <h2 id="jumlahNonKesehatan"><?= $asetNonKesehatan ?></h2>
        <p>Aset Non-Kesehatan</p>
      </div>
    </div>

    <div class="chart-container">
      <canvas id="asetChart" width="400" height="400"></canvas>
      <div id="chartLoading">Loading...</div>
    </div>
  </main>

  <footer>
    © <?=date('Y')?> RSUD Bedas Tegalluar - Sistem Aset
  </footer>

  <script>
    const ctx = document.getElementById('asetChart').getContext('2d');
    const asetChart = new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: ['Aset Kesehatan', 'Aset Non-Kesehatan'],
        datasets: [{
          label: 'Jumlah Aset',
          data: [<?= $asetKesehatan ?>, <?= $asetNonKesehatan ?>],
          backgroundColor: ['#273c75', '#fbc531'],
          borderColor: ['#ffffff', '#ffffff'],
          borderWidth: 2,
          hoverOffset: 10
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '60%',
        plugins: {
          legend: { position: 'bottom', labels: { font: { size: 14 } } },
          tooltip: { callbacks: { label: function(context){ return context.label + ': ' + context.parsed; } } }
        }
      }
    });

    // Hilangkan loading saat chart pertama kali muncul
    document.getElementById('chartLoading').style.display = 'none';

    // Fungsi update chart & card
    function updateChart() {
      fetch('get_aset_count.php')
        .then(response => response.json())
        .then(data => {
          asetChart.data.datasets[0].data = [data.asetKesehatan, data.asetNonKesehatan];
          asetChart.update();
          document.getElementById('jumlahKesehatan').textContent = data.asetKesehatan;
          document.getElementById('jumlahNonKesehatan').textContent = data.asetNonKesehatan;
        })
        .catch(err => console.error('Error fetch aset data:', err));
    }

    // Update otomatis setiap 5 detik
    setInterval(updateChart, 5000);
  </script>
</body>
</html>
