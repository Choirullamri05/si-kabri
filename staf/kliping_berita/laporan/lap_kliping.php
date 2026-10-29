<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include "inc/koneksi.php";

// ========================= Ambil Parameter Filter =========================
$date_from = trim($_GET['date_from'] ?? '');
$date_to   = trim($_GET['date_to']   ?? '');
$sentimen  = trim($_GET['sentimen']  ?? '');
$prioritas = trim($_GET['prioritas'] ?? '');

// ========================= Build WHERE FILTER =========================
$where = [];

if ($date_from)  $where[] = "tgl_input >= '$date_from'";
if ($date_to)    $where[] = "tgl_input <= '$date_to'";
if ($sentimen)   $where[] = "sentimen = '$sentimen'";
if ($prioritas)  $where[] = "kategori_program = '$prioritas'";

$whereSql = $where ? ("WHERE " . implode(" AND ", $where)) : "";

// ========================= Pagination =========================
$perPage = (int)($_GET['limit'] ?? 10);
if (!in_array($perPage, [10, 25, 50, 100], true)) $perPage = 10;

$page   = max(1, (int)($_GET['p'] ?? 1));
$offset = ($page - 1) * $perPage;

// Total untuk pagination
$qCount = $koneksi->query("SELECT COUNT(*) AS total FROM kliping_berita $whereSql");
$totalRows = $qCount->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $perPage);

// ========================= Query Data =========================
$qKliping = $koneksi->query("
  SELECT id_kliping, tgl_input, nama_media, judul_berita, sentimen, kategori_program
  FROM kliping_berita
  $whereSql
  ORDER BY tgl_input DESC, id_kliping DESC
  LIMIT $perPage OFFSET $offset
");
?>

<!-- ========================= FILTER UI ========================= -->
<div class="card shadow mb-4">
  <div class="card-header py-3">
    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-filter"></i> Filter Data</h6>
  </div>

  <div class="card-body">
    <form method="get" action="index.php">
      <input type="hidden" name="page" value="lap_kliping">

      <div class="form-row">
        <div class="form-group col-md-3">
          <label class="font-weight-bold">Dari Tanggal</label>
          <input type="date" name="date_from" class="form-control" value="<?= $date_from ?>">
        </div>

        <div class="form-group col-md-3">
          <label class="font-weight-bold">Sampai Tanggal</label>
          <input type="date" name="date_to" class="form-control" value="<?= $date_to ?>">
        </div>

        <div class="form-group col-md-3">
          <label class="font-weight-bold">Sentimen</label>
          <select name="sentimen" class="form-control">
            <option value="">-- Semua --</option>
            <option value="Positif" <?= ($sentimen=="Positif"?"selected":"") ?>>Positif</option>
            <option value="Negatif" <?= ($sentimen=="Negatif"?"selected":"") ?>>Negatif</option>
          </select>
        </div>

        <div class="form-group col-md-3">
          <label class="font-weight-bold">Kategori</label>
          <select name="prioritas" class="form-control">
            <option value="">-- Semua --</option>
            <option value="Prioritas" <?= ($prioritas=="Prioritas"?"selected":"") ?>>Prioritas</option>
            <option value="Non Prioritas" <?= ($prioritas=="Non Prioritas"?"selected":"") ?>>Non Prioritas</option>
          </select>
        </div>
      </div>

      <div class="text-right">
        <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> Tampilkan</button>
        <a href="index.php?page=lap_kliping" class="btn btn-secondary"><i class="fa fa-undo"></i> Reset</a>
      </div>
    </form>
  </div>
</div>

<!-- ========================= TABEL DATA ========================= -->
<div class="card shadow mb-4">
  <div class="card-header d-flex justify-content-between">
    <h6 class="m-0 font-weight-bold text-primary">Data Kliping Berita</h6>

    <span>
      <a href="staf/kliping_berita/laporan/export_all.php" class="btn btn-success btn-sm"><i class="fa fa-download"></i> Export Semua</a>

      <a href="staf/kliping_berita/laporan/export_filter.php?date_from=<?= $date_from ?>&date_to=<?= $date_to ?>&sentimen=<?= $sentimen ?>&prioritas=<?= $prioritas ?>"
         class="btn btn-info btn-sm"><i class="fa fa-filter"></i> Export Filter</a>
    </span>
  </div>

  <div class="card-body">
    <table class="table table-bordered text-gray-800">
      <thead>
        <tr>
          <th>No</th>
          <th>Tanggal</th>
          <th>Media</th>
          <th>Judul Berita</th>
          <th>Sentimen</th>
          <th>Kategori</th>
        </tr>
      </thead>

      <tbody>
        <?php
        $no = $offset + 1;

        if ($qKliping && $qKliping->num_rows > 0):
          while ($row = $qKliping->fetch_assoc()):
        ?>
        <tr>
          <td><?= $no++; ?></td>
          <td><?= date("d/M/Y", strtotime($row['tgl_input'])) ?></td>
          <td><?= $row['nama_media'] ?></td>
          <td><?= $row['judul_berita'] ?></td>
          <td><span class="badge <?= $row['sentimen']=="Positif"?"badge-success":"badge-danger" ?>"><?= $row['sentimen'] ?></span></td>
          <td><span class="badge <?= $row['kategori_program']=="Prioritas"?"badge-info":"badge-secondary" ?>"><?= $row['kategori_program'] ?></span></td>
        </tr>
        <?php endwhile; else: ?>
        <tr><td colspan="6" class="text-center">Tidak ada data.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>

<!-- ========================= PAGINATION ========================= -->
<?php if ($totalPages > 1): ?>
<nav>
  <ul class="pagination justify-content-center">

    <?php if ($page > 1): ?>
      <li class="page-item">
        <a class="page-link"
           href="?page=lap_kliping&p=<?= $page-1 ?>&date_from=<?= $date_from ?>&date_to=<?= $date_to ?>&sentimen=<?= $sentimen ?>&prioritas=<?= $prioritas ?>">
           &laquo; Prev</a>
      </li>
    <?php endif; ?>

    <?php for ($i=1; $i <= $totalPages; $i++): ?>
      <li class="page-item <?= ($i==$page?'active':'') ?>">
        <a class="page-link"
           href="?page=lap_kliping&p=<?= $i ?>&date_from=<?= $date_from ?>&date_to=<?= $date_to ?>&sentimen=<?= $sentimen ?>&prioritas=<?= $prioritas ?>">
           <?= $i ?>
        </a>
      </li>
    <?php endfor; ?>

    <?php if ($page < $totalPages): ?>
      <li class="page-item">
        <a class="page-link"
           href="?page=lap_kliping&p=<?= $page+1 ?>&date_from=<?= $date_from ?>&date_to=<?= $date_to ?>&sentimen=<?= $sentimen ?>&prioritas=<?= $prioritas ?>">
           Next &raquo;</a>
      </li>
    <?php endif; ?>

  </ul>
</nav>
<?php endif; ?>

  </div>
</div>
