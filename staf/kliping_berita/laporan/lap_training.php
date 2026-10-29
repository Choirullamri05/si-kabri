<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include "inc/koneksi.php";

// Ambil parameter filter
$date_from = trim($_GET['date_from'] ?? '');
$date_to   = trim($_GET['date_to']   ?? '');
$sentimen  = trim($_GET['sentimen']  ?? '');
$prioritas = trim($_GET['prioritas'] ?? '');

// Filter builder
$where = ["is_training = 1"]; // WAJIB hanya data training

if ($date_from && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_from))
  $where[] = "tgl_input >= '$date_from'";

if ($date_to && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_to))
  $where[] = "tgl_input <= '$date_to'";

if ($sentimen !== '')
  $where[] = "sentimen = '$sentimen'";

if ($prioritas !== '')
  $where[] = "kategori_program = '$prioritas'";

$whereSql = "WHERE " . implode(" AND ", $where);

// pagination
$perPage = 20;
$page = max(1, (int)($_GET['p'] ?? 1));
$offset = ($page - 1) * $perPage;

$qCount = $koneksi->query("SELECT COUNT(*) AS total FROM kliping_berita $whereSql");
$totalRows = $qCount->fetch_assoc()['total'];
$totalPage = ceil($totalRows / $perPage);

// Data training
$qData = $koneksi->query("
  SELECT tgl_input, nama_media, judul_berita, sentimen, kategori_program
  FROM kliping_berita
  $whereSql
  ORDER BY id_kliping DESC
  LIMIT $perPage OFFSET $offset
");
?>

<div class="card shadow mb-4">
  <div class="card-header py-3 d-flex justify-content-between">
    <h6 class="m-0 font-weight-bold text-primary">Data Latih (Training Set)</h6>
    <a href="staf/kliping_berita/input/export_training.php" class="btn btn-success btn-sm">
      <i class="fa fa-file-export"></i> Export Dataset Training
    </a>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-bordered text-gray-800" id="dataTable" width="100%" cellspacing="0">
        <thead>
          <tr>
            <th style="width:20px">No</th>
            <th>Tanggal</th>
            <th>Media</th>
            <th>Judul Berita</th>
            <th>Sentimen</th>
            <th>Kategori</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($qData && $qData->num_rows > 0):
            $no = $offset + 1;
            while ($r = $qData->fetch_assoc()): ?>
              <tr>
                <td><?= $no++; ?></td>
                <td><?= date("d/m/Y", strtotime($r['tgl_input'])); ?></td>
                <td><?= htmlspecialchars($r['nama_media']); ?></td>
                <td><?= htmlspecialchars($r['judul_berita']); ?></td>
                <td><?= $r['sentimen']; ?></td>
                <td><?= $r['kategori_program']; ?></td>
              </tr>
            <?php endwhile;
          else: ?>
            <tr>
              <td colspan="6" class="text-center">Belum ada data training.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>