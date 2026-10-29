<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['ses_id'])) {
  echo "<script>location='login.php'</script>";
  exit;
}

/* ====================== HELPERS ====================== */
if (!function_exists('esc')) {
  function esc($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
  }
}

if (!function_exists('foto_url')) {
  function foto_url($path) {
    $p = trim((string)$path);
    if ($p === '' || preg_match('#^https?://#i', $p)) return $p;

    // tentukan base URL aplikasi
    $base = rtrim($_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].'/si-kabri', '/');
    return $base . '/' . ltrim($path, '/');
  }
}


if (!function_exists('badgeClassFor')) {
  function badgeClassFor($kat) {
    return match(strtolower(trim((string)$kat))) {
      'galeri foto'        => 'badge-primary',
      'adventorial'        => 'badge-warning',
      'layanan masyarakat' => 'badge-success',
      default              => 'badge-secondary'
    };
  }
}

/* =============== Statistik Ringkas =============== */
$total = $bulanIni = $totGaleri = $totAdvantorial = $totLayanan = 0;

$q = $koneksi->query("SELECT COUNT(*) j FROM iklan_koran");
$total = (int)($q->fetch_assoc()['j'] ?? 0);

$q = $koneksi->query("
  SELECT COUNT(*) j FROM iklan_koran
  WHERE YEAR(tanggal_terbit)=YEAR(CURDATE())
    AND MONTH(tanggal_terbit)=MONTH(CURDATE())
");
$bulanIni = (int)($q->fetch_assoc()['j'] ?? 0);

$q = $koneksi->query("SELECT kategori_iklan, COUNT(*) j FROM iklan_koran GROUP BY kategori_iklan");
while ($r = $q->fetch_assoc()) {
  $kat = strtolower($r['kategori_iklan'] ?? '');
  $cnt = (int)($r['j'] ?? 0);
  if ($kat==='galeri foto') $totGaleri=$cnt;
  if ($kat==='adventorial') $totAdvantorial=$cnt;
  if ($kat==='layanan masyarakat') $totLayanan=$cnt;
}

/* =============== Pagination & Query =============== */
$perPage = 10;
$page    = max(1, (int)($_GET['p'] ?? 1));
$offset  = ($page - 1) * $perPage;

$rows = [];
$sql  = "SELECT id_iklan, judul_iklan, nama_media, tanggal_terbit, kategori_iklan, foto_iklan
         FROM iklan_koran
         ORDER BY tanggal_terbit DESC, id_iklan DESC
         LIMIT ?, ?";
$stmt = $koneksi->prepare($sql);
$stmt->bind_param("ii", $offset, $perPage);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) $rows[] = $row;
$stmt->close();

/* ========= Flash SweetAlert ========= */
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
if ($flash): ?>
<script>
  document.addEventListener("DOMContentLoaded", function(){
    Swal.fire({
      title: <?= json_encode($flash['title'] ?? 'Info') ?>,
      html: <?= json_encode($flash['msg'] ?? '') ?>,
      icon: <?= json_encode($flash['type'] ?? 'info') ?>
    });
  });
</script>
<?php endif; ?>

<!-- ================= UI Tampil ================= -->
<div class="alert alert-success alert-dismissible fade show">
  <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
  <h4 class="alert-heading"><i class="fas fa-info-circle"></i> Iklan Koran</h4>
  <p class="mb-1">Entri bulan ini: <b><?= esc($bulanIni) ?></b></p>
  <p class="mb-1">Total seluruh iklan: <b><?= esc($total) ?></b></p>
  <p class="mb-0 mt-2">Ringkas Kategori:
    <span class="badge badge-primary">Galeri Foto: <?= esc($totGaleri) ?></span>
    <span class="badge badge-warning">Adventorial: <?= esc($totAdvantorial) ?></span>
    <span class="badge badge-success">Layanan Masyarakat: <?= esc($totLayanan) ?></span>
  </p>
</div>

<div class="card shadow mb-4">
  <div class="card-header py-3 d-flex justify-content-between align-items-center">
    <h6 class="m-0 font-weight-bold text-primary">Data Iklan</h6>
  </div>
  <div class="card-body">
    <div class="mb-3">
      <a href="?page=i_add_iklan" class="btn btn-primary">
        <i class="fa fa-edit"></i> Tambah Data
      </a>
    </div>

  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-bordered text-gray-800" width="100%">
        <thead>
          <tr>
            <th class="text-center" width="60">No</th>
            <th class="text-center" width="100">Tanggal</th>
            <th>Judul</th>
            <th width="180">Media</th>
            <th width="140" class="text-center">Kategori</th>
            <th width="120" class="text-center">Foto</th>
            <th width="120" class="text-center">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!$rows): ?>
            <tr><td colspan="7" class="text-center text-muted">Belum ada data.</td></tr>
          <?php else: $no=$offset+1; foreach ($rows as $r): ?>
          <?php
            $id     = (int)$r['id_iklan'];
            $tgl    = $r['tanggal_terbit'] ? date('d/M/Y', strtotime($r['tanggal_terbit'])) : '-';
            $foto  = $r['foto_iklan'] ? foto_url($r['foto_iklan']) : '';
          ?>
          <tr>
            <td class="text-center"><?= $no++ ?></td>
            <td class="text-center"><?= esc($tgl) ?></td>
            <td><b><?= esc($r['judul_iklan']) ?></b></td>
            <td><?= esc($r['nama_media']) ?></td>
            <td class="text-center"><span class="badge <?= badgeClassFor($r['kategori_iklan']) ?>"><?= esc($r['kategori_iklan']) ?></span></td>
            <td class="text-center">
              <?php if ($foto): ?>
                <a href="<?= esc($foto) ?>" target="_blank">
                  <img src="<?= esc($foto) ?>" style="height:60px;object-fit:cover;border-radius:6px;">
                </a>
              <?php else: ?>
                <small class="text-muted">Tidak ada</small>
              <?php endif; ?>
            </td>
            <td class="text-center">
              <a href="?page=i_edit_iklan&id=<?= $id ?>" class="btn btn-success btn-circle btn-sm"><i class="bi bi-pencil-fill"></i></a>

              <a href="?page=i_del_iklan&id=<?= $id ?>&csrf=<?= urlencode((string)($_SESSION['csrf'] ?? '')) ?>"
                 onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')"
                 class="btn btn-danger btn-circle btn-sm">
                 <i class="fa fa-trash"></i>
              </a>
            </td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
