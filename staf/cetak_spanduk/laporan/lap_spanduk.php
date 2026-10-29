<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['ses_id'])) {
  echo "<script>location='login.php'</script>";
  exit;
}

// helper esc()
if (!function_exists('esc')) {
  function esc($s)
  {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
  }
}

/* ---------------- FILTER ---------------- */
$date_from = trim($_GET['date_from'] ?? '');
$date_to   = trim($_GET['date_to'] ?? '');
$lokasi    = trim($_GET['lokasi']    ?? '');
$q         = trim($_GET['q']         ?? '');

/* Ambil lokasi untuk opsi filter */
$lokasiList = [];
$lq = $koneksi->query("SELECT DISTINCT lokasi_pasang FROM spanduk_lokasi ORDER BY lokasi_pasang ASC");
while ($r = $lq->fetch_assoc()) $lokasiList[] = $r['lokasi_pasang'];

/* WHERE builder */
$where = [];
if ($date_from !== "") $where[] = "s.tanggal_pasang >= '{$date_from}'";
if ($date_to   !== "") $where[] = "s.tanggal_pasang <= '{$date_to}'";
if ($lokasi    !== "") $where[] = "l.lokasi_pasang LIKE '%{$lokasi}%'";
if ($q         !== "") $where[] = "s.judul_kegiatan LIKE '%{$q}%'";

$whereSql = $where ? "WHERE " . implode(" AND ", $where) : "";

/* Query data */
$sql = "
    SELECT s.id_spanduk, s.judul_kegiatan, s.tanggal_pasang, 
           l.id_lokasi, l.lokasi_pasang,
           f.id_foto, f.file_path, f.keterangan
    FROM spanduk s
    LEFT JOIN spanduk_lokasi l ON s.id_spanduk = l.id_spanduk
    LEFT JOIN spanduk_foto f   ON l.id_lokasi = f.id_lokasi
    $whereSql
    ORDER BY s.tanggal_pasang DESC, s.id_spanduk DESC
";

$query = $koneksi->query($sql);

/* Struktur ulang data */
$data = [];
while ($row = $query->fetch_assoc()) {
  $id = $row['id_spanduk'];
  if (!isset($data[$id])) {
    $data[$id] = [
      'id_spanduk' => $id,
      'judul_kegiatan' => $row['judul_kegiatan'],
      'tanggal_pasang' => $row['tanggal_pasang'],
      'lokasi' => []
    ];
  }
  if ($row['id_lokasi']) {
    $idl = $row['id_lokasi'];
    if (!isset($data[$id]['lokasi'][$idl])) {
      $data[$id]['lokasi'][$idl] = [
        'lokasi_pasang' => $row['lokasi_pasang'],
        'foto' => []
      ];
    }
    if ($row['id_foto']) {
      $data[$id]['lokasi'][$idl]['foto'][] = [
        'file_path'   => $row['file_path'],
        'keterangan' => $row['keterangan']
      ];
    }
  }
}
?>
<div class="card shadow mb-4">

  <div class="card-header py-3">
    <h6 class="m-0 font-weight-bold text-primary">Data Iklan Koran</h6>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-bordered" width="100%" cellspacing="0">
        <thead>
          <tr>
            <th width="40" class="text-center">No</th>
            <th width="120" class="text-center">Tanggal</th>
            <th width="120" class="text-center">Judul Kegiatan</th>
            <th width="200">Lokasi</th>
            <th width="200" class="text-center">Foto</th>
            <th width="80" class="text-center">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($data)): ?>
            <tr>
              <td colspan="6" class="text-center text-muted">Tidak ada data</td>
            </tr>
            <?php else: $no = 1;
            foreach ($data as $item): ?>
              <tr>
                <td class="text-center"><?= $no++ ?></td>
                <td class="text-center"><?= date('d M Y', strtotime($item['tanggal_pasang'])) ?></td>
                <td class="font-weight-bold"><?= esc($item['judul_kegiatan']) ?></td>
                <td>
                  <?php foreach ($item['lokasi'] as $lok): ?>
                    <div>- <?= esc($lok['lokasi_pasang']) ?></div>
                  <?php endforeach; ?>
                </td>
                <td class="text-center">
                  <?php
                  $printed = false;
                  foreach ($item['lokasi'] as $lok):
                    foreach ($lok['foto'] as $foto):
                  ?>
                      <a href="javascript:void(0)" onclick="showFoto('<?= esc($foto['file_path']) ?>')" class="d-inline-block m-1">
                        <img src="<?= esc($foto['file_path']) ?>" style="height:60px;object-fit:cover;border-radius:6px;cursor:pointer;">
                      </a>

                  <?php $printed = true;
                    endforeach;
                  endforeach; ?>
                  <?php if (!$printed): ?>
                    <span class="text-muted small">Tidak ada</span>
                  <?php endif; ?>
                </td>
                <td class="text-center text-nowrap">
                  <a href="?page=detail_lap_spanduk&id=<?= $item['id_spanduk'] ?>"
                    class="btn btn-info btn-circle btn-sm" title="Detail">
                    <i class="fa fa-eye"></i>
                  </a>
                </td>
              </tr>
          <?php endforeach;
          endif; ?>
        </tbody>
        <script>
          function showFoto(src) {
            document.getElementById("previewFoto").src = src;
            $('#modalFoto').modal('show');
          }
        </script>

      </table>
      <!-- Modal Preview Foto -->
      <div class="modal fade" id="modalFoto" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
          <div class="modal-content bg-dark p-2" style="border-radius:10px;">
            <img id="previewFoto" src="" class="img-fluid rounded" alt="Preview Foto">
          </div>
        </div>
      </div>
    </div>
  </div>
</div>