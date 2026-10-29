<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['ses_id'])) {
  echo "<script>location='login.php'</script>";
  exit;
}

/* ====================== HELPER ====================== */
if (!function_exists('esc')) {
  function esc($s)
  {
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
  function badgeClassFor($kat)
  {
    $map = [
      'galeri foto'        => 'badge-primary',
      'adventorial'        => 'badge-warning',
      'layanan masyarakat' => 'badge-success',
    ];
    return $map[strtolower(trim((string)$kat))] ?? 'badge-secondary';
  }
}

/* ================== ENUM KATEGORI ================== */
$kategoriOptions = [];
$qEnum = $koneksi->query("
  SELECT COLUMN_TYPE
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'iklan_koran'
    AND COLUMN_NAME = 'kategori_iklan'
  LIMIT 1
");

if ($qEnum && $row = $qEnum->fetch_assoc()) {
  if (preg_match("/^enum\\((.+)\\)$/i", $row['COLUMN_TYPE'], $m)) {
    $kategoriOptions = array_map(fn($v) => trim(trim($v), "'\""), explode(',', $m[1]));
  }
}

/* ======================= FILTER ======================= */
$date_from = trim($_GET['date_from'] ?? '');
$date_to   = trim($_GET['date_to'] ?? '');
$kat       = trim($_GET['kat']       ?? '');
$media     = trim($_GET['media']     ?? '');
$q         = trim($_GET['q']         ?? '');

/* ======================= PAGING ======================= */
$perPage = (int)($_GET['limit'] ?? 10);
if (!in_array($perPage, [10, 25, 50, 100], true)) $perPage = 10;

$page   = max(1, (int)($_GET['p'] ?? 1));
$offset = ($page - 1) * $perPage;

/* ================== WHERE BUILDER ================== */
$where = [];
$types = '';
$args  = [];

if ($date_from && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_from)) {
  $where[] = "tanggal_terbit >= ?";
  $types .= 's';
  $args[] = $date_from;
}

if ($date_to && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_to)) {
  $where[] = "tanggal_terbit <= ?";
  $types .= 's';
  $args[] = $date_to;
}

if ($kat !== '' && in_array($kat, $kategoriOptions)) {  // non strict, biar matching
  $where[] = "kategori_iklan = ?";
  $types .= 's';
  $args[] = $kat;
}

if ($media !== '') {
  $where[] = "nama_media LIKE ?";
  $types .= 's';
  $args[] = "%{$media}%";
}

if ($q !== '') {
  $where[] = "(judul_iklan LIKE ? OR nama_media LIKE ?)";
  $types .= 'ss';
  $args[] = "%{$q}%";
  $args[] = "%{$q}%";
}

$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

/* ================== QUERY DATA ================== */
$rows = [];
$sql = "
  SELECT id_iklan, judul_iklan, nama_media, tanggal_terbit,
         kategori_iklan, foto_iklan
  FROM iklan_koran
  $whereSql
  ORDER BY tanggal_terbit DESC, id_iklan DESC
  LIMIT ? OFFSET ?
";

if ($stmt = $koneksi->prepare($sql)) {

  $typesFinal = $types . 'ii';
  $bindArgs   = array_merge($args, [$perPage, $offset]);

  $stmt->bind_param($typesFinal, ...$bindArgs);

  if ($stmt->execute()) {
    $res = $stmt->get_result();
    $rows = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
  }
  $stmt->close();
}
?>


<!-- =================== Filter =================== -->
<div class="card shadow mb-4">
  <div class="card-header py-3">
    <h6 class="m-0 font-weight-bold text-primary">Filter</h6>
  </div>

  <div class="card-body">
    <form method="get" class="mb-0">
      <input type="hidden" name="page" value="lap_iklan">

      <div class="form-row">
        <!-- Dari Tanggal -->
        <div class="form-group col-md-3">
          <label>Dari Tanggal</label>
          <input type="date" name="date_from" class="form-control" value="<?= esc($date_from) ?>">
        </div>

        <!-- Sampai Tanggal -->
        <div class="form-group col-md-3">
          <label>Sampai Tanggal</label>
          <input type="date" name="date_to" class="form-control" value="<?= esc($date_to) ?>">
        </div>

        <!-- Kategori -->
        <div class="form-group col-md-3">
          <label>Kategori</label>
          <select name="kat" class="form-control">
            <option value="">-- Semua --</option>
            <?php foreach ($kategoriOptions as $opt): ?>
              <option value="<?= esc($opt) ?>" <?= $kat === $opt ? 'selected' : '' ?>>
                <?= esc($opt) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Tombol Aksi -->
        <div class="form-group col-md-3 d-flex align-items-end justify-content-end">
          <button type="submit" class="btn btn-primary mr-2">
            <i class="fas fa-search"></i> Terapkan
          </button>
          <a href="?page=lap_iklan" class="btn btn-secondary">
            <i class="fas fa-undo"></i> Reset
          </a>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- =================== Data =================== -->
<div class="card shadow mb-4">
  <div class="card-header py-3">
    <h6 class="m-0 font-weight-bold text-primary">Data Iklan Koran</h6>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-bordered text-gray-800" id="dataTable" width="100%" cellspacing="0">
        <thead>
          <tr>
            <th style="width:60px">No</th>
            <th style="width:120px">Tanggal</th>
            <th>Media</th>
            <th>Judul Iklan</th>
            <th style="width:140px">Kategori</th>
            <th style="width:120px">Foto</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $no = $offset + 1;
          if ($rows):
            foreach ($rows as $r):
              $tgl   = $r['tanggal_terbit'] ? date("d/M/Y", strtotime($r['tanggal_terbit'])) : '-';
              $media = esc($r['nama_media'] ?? '');
              $judul = esc($r['judul_iklan'] ?? '');
              $kat   = esc($r['kategori_iklan'] ?? '');
              $foto  = $r['foto_iklan'] ? foto_url($r['foto_iklan']) : '';

              // Badge kategori
              $badgeKat = badgeClassFor($kat);
          ?>
              <tr>
                <td class="text-center"><?= $no++; ?></td>
                <td class="text-center"><?= $tgl; ?></td>
                <td><?= $media; ?></td>
                <td><?= $judul; ?></td>
                <td class="text-center">
                  <span class="badge <?= $badgeKat; ?>"><?= $kat ?: '-'; ?></span>
                </td>
                <td class="text-center">
                  <?php if ($foto): ?>
                    <a href="<?= esc($foto) ?>" target="_blank" rel="noopener">
                      <img src="<?= esc($foto) ?>" alt="Foto Iklan" style="height:60px; object-fit:cover; border-radius:6px;">
                    </a>
                  <?php else: ?>
                    <span class="text-muted small">Tidak ada</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php
            endforeach;
          else:
            ?>
            <tr>
              <td colspan="6" class="text-center">Belum ada data iklan.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>