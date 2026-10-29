<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// Ambil nama & level dari session login
$data_nama  = $_SESSION["ses_user"];
$data_level = $_SESSION["ses_level"];

// === Hitung jumlah pengguna ===
$res_pengguna = $koneksi->query("SELECT COUNT(id_pengguna) AS jml FROM pengguna");
$jml_pengguna = $res_pengguna->fetch_assoc()['jml'] ?? 0;

// === Hitung jumlah berita kliping ===
$res_kliping = $koneksi->query("SELECT COUNT(id_kliping) AS jml FROM kliping_berita");
$jml_kliping = $res_kliping->fetch_assoc()['jml'] ?? 0;

// === Total sentimen positif ===
$res_pos  = $koneksi->query("SELECT COUNT(id_kliping) AS jml FROM kliping_berita WHERE sentimen='Positif'");
$jml_pos = $res_pos->fetch_assoc()['jml'] ?? 0;

// === Total sentimen negatif ===
$res_neg  = $koneksi->query("SELECT COUNT(id_kliping) AS jml FROM kliping_berita WHERE sentimen='Negatif'");
$jml_neg = $res_neg->fetch_assoc()['jml'] ?? 0;

// === Total iklan koran ===
$res_iklan = $koneksi->query("SELECT COUNT(id_iklan) AS jml FROM iklan_koran");
$jml_iklan = $res_iklan->fetch_assoc()['jml'] ?? 0;

// === Total spanduk ===
$res_spanduk = $koneksi->query("SELECT COUNT(id_spanduk) AS jml FROM spanduk");
$jml_spanduk = $res_spanduk->fetch_assoc()['jml'] ?? 0;


// ====================== Hitung Prioritas vs Non Prioritas ======================
$prioritas = 0;
$non_prioritas = 0;

$qPrior = mysqli_query($koneksi, "
    SELECT kategori_program, COUNT(*) as total 
    FROM kliping_berita 
    GROUP BY kategori_program
");

while ($row = mysqli_fetch_assoc($qPrior)) {
    if ($row['kategori_program'] === 'Prioritas') {
        $prioritas = $row['total'];
    } else {
        $non_prioritas = $row['total'];
    }
}

// ====================== Hitung Sentimen ======================
$positif = 0;
$negatif = 0;

$qSent = mysqli_query($koneksi, "
    SELECT sentimen, COUNT(*) as total 
    FROM kliping_berita 
    GROUP BY sentimen
");

while ($row = mysqli_fetch_assoc($qSent)) {
    if ($row['sentimen'] === 'Positif') {
        $positif = $row['total'];
    } else {
        $negatif = $row['total'];
    }
}
?>

<div class="container-fluid">
    <!-- Content Row -->
    <div class="row">
        <!-- Earnings (Monthly) Card Example -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Total Berita Kliping</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $jml_kliping; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-newspaper fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Prioritas</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $prioritas; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-star fa-2x text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Non Prioritas</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $non_prioritas; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-times-circle fa-2x text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Sentimen Positif</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $jml_pos; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-smile fa-2x text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Sentimen Negatif</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $jml_neg; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-frown fa-2x text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Iklan Koran</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $jml_iklan; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-ad fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Cetak Spanduk</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $jml_spanduk; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-flag fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-secondary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">
                                Pengguna</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $jml_pengguna; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-secondary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tambahkan di bawah card container -->
<div class="row">
    <!-- Chart Sentimen -->
    <div class="col-xl-6 col-lg-6">
        <div class="card shadow mb-4"> <!-- samakan mb biar sejajar -->
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Distribusi Kategori</h6>
            </div>
            <!-- Tinggi fix -->
            <div class="card-body" style="height:350px;">
                <canvas id="kategoriChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Chart Data Sistem -->
    <div class="col-xl-6 col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-success">Distribusi Sentimen</h6>
            </div>
            <!-- Tinggi fix sama dengan sebelah -->
            <div class="card-body" style="height:350px;">
                <canvas id="sentimenChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Script Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Pie Chart Sentimen
    const ctxKategori = document.getElementById('kategoriChart');
    new Chart(ctxKategori, {
        type: 'pie',
        data: {
            labels: ['prioritas', 'Non Prioritas'],
            datasets: [{
                data: [<?= $prioritas; ?>, <?= $non_prioritas; ?>],
                backgroundColor: ['#4e73df', '#f6c23e'],
                hoverOffset: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false, // biar ikutin tinggi card-body
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Bar Chart Data Sistem
    const ctxSentimen = document.getElementById('sentimenChart');
    new Chart(ctxSentimen, {
        type: 'pie',
        data: {
            labels: ['Positif', 'Negatif'],
            datasets: [{
                data: [<?= $positif; ?>, <?= $negatif; ?>],
                backgroundColor: ['#36b9cc', '#e74a3b'],
                hoverOffset: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false, // biar ikutin tinggi card-body
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
</script>