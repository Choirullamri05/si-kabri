<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['ses_id'])) {
    header("Location: login.php");
    exit;
}


require_once __DIR__ . "/inc/koneksi.php";

$id_pengguna = (int) $_SESSION['ses_id'];
$namaUser = $_SESSION['ses_nama'] ?? 'User';
$level    = $_SESSION['ses_level'] ?? '';
$foto     = $_SESSION['ses_foto'] ?? null;


$sql = "
  SELECT 
    p.username,
    p.level,
    b.nama_lengkap,
    b.foto_profile
  FROM pengguna p
  LEFT JOIN biodata b ON b.id_pengguna = p.id_pengguna
  WHERE p.id_pengguna = ?
  LIMIT 1
";
$stmt = mysqli_prepare($koneksi, $sql);
mysqli_stmt_bind_param($stmt, 'i', $id_pengguna);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($res) ?: [];
mysqli_stmt_close($stmt);

$data_username = $user['username'] ?? ($_SESSION['ses_username'] ?? 'user');
$data_nama     = $user['nama_lengkap'] ?? ($_SESSION['ses_nama'] ?? $data_username);


$data_role_raw = $user['level'] ?? ($_SESSION['ses_level'] ?? 'Staf');
$data_role = ucfirst(strtolower($data_role_raw));
if (!in_array($data_role, ['Admin', 'Staf', 'Pimpinan'], true)) $data_role = 'Staf';

$role_label_map = ['Admin' => 'Administrator', 'Staf' => 'Pegawai', 'Pimpinan' => 'Pimpinan'];
$role_label = $role_label_map[$data_role];


$foto_db   = $user['foto_profile'] ?? null;
$foto_sess = $_SESSION['ses_foto'] ?? null;
$foto_name = $foto_db ?: $foto_sess ?: null;


$esc = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');


function esc($s)
{
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}


$fotoUrl = (!empty($foto_name))
    ? "uploads/avatars/" . rawurlencode($foto_name)
    : "dist/img/undraw_profile_1.svg";

$actionPages = [
    'i_add_iklan_do'  => __DIR__ . '/staf/iklan_koran/input/add_iklan_do.php',
    'i_edit_iklan_do' => __DIR__ . '/staf/iklan_koran/input/edit_iklan_do.php',
    'i_del_iklan'     => __DIR__ . '/staf/iklan_koran/input/del_iklan.php',
];

if (!empty($_GET['page']) && isset($actionPages[$_GET['page']])) {
    require $actionPages[$_GET['page']];
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>SI-KABRI</title>
    <link rel="icon" href="dist/img/kominfo_logo.png" type="image/x-icon">

    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom styles for this template-->
    <link href="dist/css/sweetalert2.min.css" rel="stylesheet">
    <link href="dist/css/sb-admin-2.min.css" rel="stylesheet">
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <script src="vendor/sweetalert/alert.js"></script>
</head>

<body id="page-top">
    <!-- Pembungkus Halaman -->
    <div id="wrapper">
        <!-- Menu Samping -->
        <ul class="navbar-nav bg-gradient-dark sidebar sidebar-dark accordion" id="accordionSidebar">
            <!-- Logo -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.php">
                <div class="sidebar-brand-icon rotate-n-15">
                    <img src="dist/img/kominfo_logo.png" alt="Logo" style="width:40px; height:40px;">
                </div>
                <div class="sidebar-brand-text mx-3">SI-KABRI</div>
            </a>
            <hr class="sidebar-divider">

            <?php
            if ($data_role  == "Admin") {
            ?>

                <li class="nav-item active">
                    <a class="nav-link" href="?page=MyApp/admin">
                        <i class="fas fa-fw fa-tachometer-alt"></i>
                        <span>Dashboard</span></a>
                </li>

                <!-- Garis Pemisah -->
                <hr class="sidebar-divider">

                <!-- Heading -->
                <div class="sidebar-heading">
                    Menu Media
                </div>

                <!-- Nav Item - Pages Collapse Menu -->
                <li class="nav-item">
                    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTwo"
                        aria-expanded="false" aria-controls="collapseTwo">
                        <i class="fas fa-fw fa-cog"></i>
                        <span>Menu Input</span>
                    </a>
                    <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                        <div class="collapse-inner rounded p-2" style="background: #f1f5fb;">
                            <h6 class="collapse-header text-primary font-weight-bold">Pilih Input</h6>
                            <a class="collapse-item d-flex align-items-center" href="?page=i_data_klp">
                                <i class="fas fa-newspaper fa-sm fa-fw mr-2 text-primary"></i> Kliping Berita
                            </a>
                            <a class="collapse-item d-flex align-items-center" href="?page=i_data_iklan">
                                <i class="fas fa-ad fa-sm fa-fw mr-2 text-success"></i> Iklan Koran
                            </a>
                            <a class="collapse-item d-flex align-items-center" href="?page=i_data_spanduk">
                                <i class="fas fa-flag fa-sm fa-fw mr-2 text-danger"></i> Arsip Spanduk
                            </a>
                        </div>
                    </div>
                </li>


                <!-- Divider -->
                <hr class="sidebar-divider">

                <!-- Heading -->
                <div class="sidebar-heading">
                    Menu Laporan
                </div>

                <!-- Nav Item - Pages Collapse Menu -->
                <li class="nav-item">
                    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePages"
                        aria-expanded="true" aria-controls="collapsePages">
                        <i class="fas fa-fw fa-folder"></i>
                        <span>Laporan Data</span>
                    </a>
                    <div id="collapsePages" class="collapse" aria-labelledby="headingPages" data-parent="#accordionSidebar">
                        <div class="collapse-inner rounded p-2 " style="background: #f1f5fb;">
                            <h6 class="collapse-header text-primary font-weight-bold">Menu Laporan Data:</h6>
                            <a class="collapse-item d-flex align-items-center" href="?page=lap_kliping">
                                <i class="fas fa-newspaper fa-sm fa-fw mr-2 text-primary"></i>Kliping berita
                            </a>
                            <a class="collapse-item d-flex align-items-center" href="?page=lap_training">
                                <i class="bi bi-database-fill-gear fa-sm fa-fw mr-2 text-warning"></i>Training Set Berita
                            </a>
                            <a class="collapse-item d-flex align-items-center" href="?page=lap_iklan">
                                <i class="fas fa-ad fa-sm fa-fw mr-2 text-success"></i>Iklan Koran
                            </a>
                            <a class="collapse-item d-flex align-items-center" href="?page=lap_spanduk">
                                <i class="fas fa-flag fa-sm fa-fw mr-2 text-danger"></i>Arsip Spanduk
                            </a>
                        </div>
                </li>

                <!-- Divider -->
                <hr class="sidebar-divider d-none d-md-block">
                <div class="sidebar-heading">
                    setting
                </div>
                <!-- Nav Item - Profil Collapse Menu -->
                <li class="nav-item">
                    <a class="nav-link collapsed" href="?page=MyApp/data_pengguna">
                        <i class="fas fa-users"></i>
                        <span>Manajemen User</span>
                    </a>
                </li>

                <hr class="sidebar-divider d-none d-md-block">
                <div class="sidebar-heading">
                    Bantuan
                </div>
                <li class="nav-item">
                    <a class="nav-link" href="?page=faq">
                        <i class="fas fa-question-circle"></i>
                        <span>FAQ & Panduan</span>
                    </a>
                </li>




            <?php
            } elseif ($data_role == "Staf") {
            ?>

                <li class="nav-item active">
                    <a class="nav-link" href="?page=MyApp/pegawai">
                        <i class="fas fa-fw fa-tachometer-alt"></i>
                        <span>Dashboard</span></a>
                </li>

                <!-- Garis Pemisah -->
                <hr class="sidebar-divider">

                <!-- Heading -->
                <div class="sidebar-heading">
                    Menu Media
                </div>

                <!-- Nav Item - Pages Collapse Menu -->
                <li class="nav-item">
                    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTwo"
                        aria-expanded="false" aria-controls="collapseTwo">
                        <i class="fas fa-fw fa-cog"></i>
                        <span>Menu Input</span>
                    </a>
                    <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                        <div class="collapse-inner rounded p-2" style="background: #f1f5fb;">
                            <h6 class="collapse-header text-primary font-weight-bold">Pilih Input</h6>
                            <a class="collapse-item d-flex align-items-center" href="?page=i_data_klp">
                                <i class="fas fa-newspaper fa-sm fa-fw mr-2 text-primary"></i> Kliping Berita
                            </a>
                            <a class="collapse-item d-flex align-items-center" href="?page=i_data_iklan">
                                <i class="fas fa-ad fa-sm fa-fw mr-2 text-success"></i> Iklan Koran
                            </a>
                            <a class="collapse-item d-flex align-items-center" href="?page=i_data_spanduk">
                                <i class="fas fa-flag fa-sm fa-fw mr-2 text-danger"></i> Arsip Spanduk
                            </a>
                        </div>
                    </div>
                </li>


                <!-- Divider -->
                <hr class="sidebar-divider">

                <!-- Heading -->
                <div class="sidebar-heading">
                    Menu Laporan
                </div>

                <!-- Nav Item - Pages Collapse Menu -->
                <li class="nav-item">
                    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePages"
                        aria-expanded="true" aria-controls="collapsePages">
                        <i class="fas fa-fw fa-folder"></i>
                        <span>Laporan Data</span>
                    </a>
                    <div id="collapsePages" class="collapse" aria-labelledby="headingPages" data-parent="#accordionSidebar">
                        <div class="collapse-inner rounded p-2 " style="background: #f1f5fb;">
                            <h6 class="collapse-header text-primary font-weight-bold">Menu Laporan Data:</h6>
                            <a class="collapse-item d-flex align-items-center" href="?page=lap_kliping">
                                <i class="fas fa-newspaper fa-sm fa-fw mr-2 text-primary"></i>Kliping berita
                            </a>
                            <a class="collapse-item d-flex align-items-center" href="?page=lap_training">
                                <i class="bi bi-database-fill-gear fa-sm fa-fw mr-2 text-warning"></i>Training Set Berita
                            </a>
                            <a class="collapse-item d-flex align-items-center" href="?page=lap_iklan">
                                <i class="fas fa-ad fa-sm fa-fw mr-2 text-success"></i>Iklan Koran
                            </a>
                            <a class="collapse-item d-flex align-items-center" href="?page=lap_spanduk">
                                <i class="fas fa-flag fa-sm fa-fw mr-2 text-danger"></i>Arsip Spanduk
                            </a>
                        </div>
                </li>

                <!-- Divider -->
                <hr class="sidebar-divider d-none d-md-block">
                <div class="sidebar-heading">
                    setting
                </div>

                <!-- Nav Item - Profil Collapse Menu -->
                <li class="nav-item">
                    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseProfil"
                        aria-expanded="true" aria-controls="collapseProfil">
                        <i class="fas fa-fw fa-user"></i>
                        <span>Profil</span>
                    </a>
                    <div id="collapseProfil" class="collapse" aria-labelledby="headingProfil"
                        data-parent="#accordionSidebar">
                        <div class="bg-white py-2 collapse-inner rounded">
                            <h6 class="collapse-header">Pengaturan Akun:</h6>
                            <a class="collapse-item" href="?page=edit_proile">
                                <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                                Pengaturan
                            </a>
                        </div>
                    </div>
                </li>

                <hr class="sidebar-divider d-none d-md-block">
                <div class="sidebar-heading">
                    Bantuan
                </div>
                <li class="nav-item">
                    <a class="nav-link" href="?page=faq">
                        <i class="fas fa-question-circle"></i>
                        <span>FAQ & Panduan</span>
                    </a>
                </li>

            <?php
            }
            ?>

            <hr class="sidebar-divider d-none d-md-block">

            <!-- Sidebar Toggler -->
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>
        </ul>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 shadow">
                    <div class="container-fluid">
                        <!-- Sidebar Toggle (Mobile) -->
                        <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                            <i class="fa fa-bars"></i>
                        </button>

                        <!-- Page Heading -->
                        <div class="d-flex align-items-center">
                            <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
                        </div>

                        <!-- Navbar Right -->
                        <ul class="navbar-nav ml-auto">
                            <!-- User Avatar Dropdown -->
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <img src="<?= $esc($fotoUrl) ?>" alt="avatar" class="rounded-circle" width="40" height="40">
                                </a>
                                <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in p-3" style="min-width: 250px;">
                                    <div class="text-center mb-3">
                                        <img src="<?= $esc($fotoUrl) ?>" alt="avatar"
                                            class="rounded-circle mb-2"
                                            style="width:80px; height:80px; object-fit:cover;">
                                        <h6 class="mb-0 font-weight-bold"><?= $esc($data_nama) ?></h6>
                                        <small class="text-muted"><?= $esc($role_label) ?></small>
                                    </div>

                                    <div class="d-grid gap-2">
                                        <a href="?page=edit_proile" class="btn btn-outline-primary btn-sm btn-block">Profile</a>
                                        <button class="btn btn-outline-danger btn-sm btn-block" data-toggle="modal" data-target="#logoutModal">Sign out</button>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </nav>


                <!-- End of Topbar -->

                <!-- Content -->
                <section class="content">
                    <div class="container-fluid">
                        <?php
                        if (isset($_GET['page'])) {
                            $hal = $_GET['page'];
                            switch ($hal) {
                                //Klik Halaman Home Pengguna
                                case 'MyApp/admin':
                                    include "home/admin.php";
                                    break;
                                case 'MyApp/pegawai':
                                    include "home/staf.php";
                                    break;

                                //Pengguna
                                case 'MyApp/data_pengguna':
                                    include "admin/pengguna/data_pengguna.php";
                                    break;
                                case 'MyApp/add_pengguna':
                                    include "admin/pengguna/add_pengguna.php";
                                    break;
                                case 'MyApp/edit_pengguna':
                                    include "admin/pengguna/edit_pengguna.php";
                                    break;
                                case 'MyApp/del_pengguna':
                                    include "admin/pengguna/del_pengguna.php";
                                    break;

                                //input kliping berita
                                case 'i_data_klp':
                                    include "staf/kliping_berita/input/data_kliping.php";
                                    break;
                                case 'i_add_klp':
                                    include "staf/kliping_berita/input/add_kliping.php";
                                    break;
                                case 'i_del_klp':
                                    include "staf/kliping_berita/input/del_kliping.php";
                                    break;
                                case 'i_edit_klp':
                                    include "staf/kliping_berita/input/edit_kliping.php";
                                    break;

                                //laporan kliping
                                case 'lap_kliping':
                                    include "staf/kliping_berita/laporan/lap_kliping.php";
                                    break;
                                case 'lap_training':
                                    include "staf/kliping_berita/laporan/lap_training.php";
                                    break;


                                //input iklan koran
                                case 'i_data_iklan':
                                    include "staf/iklan_koran/input/data_iklan.php";
                                    break;
                                case 'i_add_iklan':
                                    include "staf/iklan_koran/input/add_iklan.php";
                                    break;
                                case 'i_del_iklan':
                                    include "staf/iklan_koran/input/del_iklan.php";
                                    break;
                                case 'i_edit_iklan':
                                    include "staf/iklan_koran/input/edit_iklan.php";
                                    break;

                                // laporan iklan koran
                                case 'lap_iklan':
                                    include "staf/iklan_koran/laporan/lap_iklan_koran.php";
                                    break;

                                case 'i_data_spanduk':
                                    include "staf/cetak_spanduk/input/data_spanduk.php";
                                    break;
                                case 'i_add_spanduk':
                                    include "staf/cetak_spanduk/input/add_spanduk.php";
                                    break;
                                case 'i_edit_spanduk':
                                    include "staf/cetak_spanduk/input/edit_spanduk.php";
                                    break;
                                case 'i_del_spanduk':
                                    include "staf/cetak_spanduk/input/del_spanduk.php";
                                    break;
                                case 'i_detail_spanduk':
                                    include "staf/cetak_spanduk/input/detail_spanduk.php";
                                    break;

                                //laporan spanduk
                                case 'lap_spanduk':
                                    include "staf/cetak_spanduk/laporan/lap_spanduk.php";
                                    break;
                                case 'detail_lap_spanduk':
                                    include "staf/cetak_spanduk/laporan/detail_lap_spanduk.php";
                                    break;

                                //Edit Profile
                                case 'data_proile':
                                    include "admin/profile/data_profile.php";
                                    break;
                                case 'edit_proile':
                                    include "admin/profile/edit_profile.php";
                                    break;
                                
                                //Bantuan
                                case 'faq':
                                    include "faq.php";
                                    break;

                                //default
                                default:
                                    echo "<center><h1> ERROR !</h1></center>";
                                    break;
                            }
                        } else {
                            // Halaman default berdasarkan level
                            if ($data_role == "Admin") {
                                include "home/admin.php";
                            } elseif ($data_role == "Staf") {
                                include "home/staf.php";
                            }
                        }
                        ?>
                    </div>
                </section>
            </div>
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; Divisi Komunikasi Publik Diskominfo Kota Medan 2025</span>
                    </div>
                </div>
            </footer>

            <!-- Logout Modal-->
            <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Apakah Anda yakin ingin keluar?</h5>
                            <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                        </div>
                        <div class="modal-body">Klik "Keluar" di bawah ini jika Anda sudah siap mengakhiri sesi</div>
                        <div class="modal-footer">
                            <button class="btn btn-secondary" type="button" data-dismiss="modal">Batal</button>
                            <a class="btn btn-primary" href="logout.php">Keluar</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bootstrap core JavaScript-->
            <script src="vendor/jquery/jquery.min.js"></script>
            <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

            <!-- Core plugin JavaScript-->
            <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

            <!-- Custom scripts for all pages-->
            <script src="build/js/sb-admin-2.min.js"></script>
            <script src="vendor/sweetalert/alert.js"></script>

            <!-- Page level plugins -->
            <script src="vendor/datatables/jquery.dataTables.min.js"></script>
            <script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>
            <script src="vendor/sweetalert/alert.js"></script>
            <script src="vendor/chart.js/Chart.min.js"></script>

            <!-- Page level custom scripts -->
            <script src="build/js/demo/datatables-demo.js"></script>
            <script src="build/js/demo/chart-area-demo.js"></script>
            <script src="build/js/demo/chart-pie-demo.js"></script>

        </div>
    </div>
</body>

</html>