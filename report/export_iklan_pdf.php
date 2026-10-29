<?php
include "../inc/koneksi.php";

/* Helper URL Foto */
function foto_url($path) {
    if (!$path) return '';
    if (preg_match('#^https?://#i', $path)) return $path;
    $base = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
    $base .= "://".$_SERVER['HTTP_HOST'];
    $base .= rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
    return $base . '/../' . ltrim($path, '/');
}

/* Filter */
$date_from = trim($_GET['date_from'] ?? '');
$date_to   = trim($_GET['date_to'] ?? '');
$kat       = trim($_GET['kat'] ?? '');
$media     = trim($_GET['media'] ?? '');
$q         = trim($_GET['q'] ?? '');

$where = [];
if ($date_from) $where[] = "tanggal_terbit >= '".$koneksi->real_escape_string($date_from)."'";
if ($date_to)   $where[] = "tanggal_terbit <= '".$koneksi->real_escape_string($date_to)."'";
if ($kat)       $where[] = "kategori_iklan = '".$koneksi->real_escape_string($kat)."'";
if ($media)     $where[] = "nama_media LIKE '%".$koneksi->real_escape_string($media)."%'";
if ($q)         $where[] = "(judul_iklan LIKE '%".$koneksi->real_escape_string($q)."%' OR nama_media LIKE '%".$koneksi->real_escape_string($q)."%')";

$whereSql = $where ? "WHERE ".implode(" AND ", $where) : "";

$sql = $koneksi->query("
    SELECT id_iklan, tanggal_terbit, nama_media, judul_iklan, kategori_iklan, foto_iklan
    FROM iklan_koran
    $whereSql
    ORDER BY tanggal_terbit ASC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Laporan Data Iklan</title>
  <style>
    /* Atur halaman ke ukuran A4 */
    @page {
      size: A4 portrait;
      margin: 20mm;
    }
    body { font-family: Arial, sans-serif; font-size: 12px; }
    .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
    .header h1 { margin: 0; font-size: 18px; }
    .header p { margin: 0; font-size: 12px; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th, td { border: 1px solid black; padding: 6px; }
    th { background: #f2f2f2; }
    .center { text-align: center; }
    img { border-radius: 4px; max-height: 60px; object-fit: cover; }
  </style>
</head>
<body>
  <div class="header">
    <h1>LAPORAN DATA IKLAN KORAN</h1>
    <p>Dicetak: <?= date("d/m/Y H:i") ?></p>
  </div>

  <table>
    <thead>
      <tr>
        <th style="width:40px;">No</th>
        <th style="width:80px;">Tanggal</th>
        <th style="width:120px;">Media</th>
        <th>Judul Iklan</th>
        <th style="width:100px;">Kategori</th>
        <th style="width:120px;">Foto</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($sql && $sql->num_rows > 0): 
        $no = 1;
        while ($row = $sql->fetch_assoc()):
          $fotoUrl = $row['foto_iklan'] ? foto_url($row['foto_iklan']) : '';
      ?>
      <tr>
        <td class="center"><?= $no++; ?></td>
        <td class="center"><?= $row['tanggal_terbit'] ? date("d/M/Y", strtotime($row['tanggal_terbit'])) : '-' ?></td>
        <td><?= htmlspecialchars($row['nama_media']) ?></td>
        <td><?= htmlspecialchars($row['judul_iklan']) ?></td>
        <td class="center"><?= htmlspecialchars($row['kategori_iklan']) ?></td>
        <td class="center">
          <?php if ($fotoUrl): ?>
            <img src="<?= $fotoUrl ?>" alt="foto iklan">
          <?php else: ?>
            <span class="text-muted small">-</span>
          <?php endif; ?>
        </td>
      </tr>
      <?php endwhile; else: ?>
        <tr><td colspan="6" class="center">Tidak ada data.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>

  <script>
    window.print();
  </script>
</body>
</html>
