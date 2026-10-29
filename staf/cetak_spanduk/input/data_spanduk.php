<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['ses_id'])) {
  echo "<script>location='login.php'</script>";
  exit;
}

$sql = "
    SELECT s.id_spanduk, s.judul_kegiatan, s.tanggal_pasang, 
           l.id_lokasi, l.lokasi_pasang,
           f.id_foto, f.file_path, f.keterangan
    FROM spanduk s
    LEFT JOIN spanduk_lokasi l ON s.id_spanduk = l.id_spanduk
    LEFT JOIN spanduk_foto f ON l.id_lokasi = f.id_lokasi
    ORDER BY s.tanggal_pasang DESC, s.id_spanduk DESC
";

$query = $koneksi->query($sql);


$data = [];
while ($row = $query->fetch_assoc()) {
  $id_spanduk = $row['id_spanduk'];
  if (!isset($data[$id_spanduk])) {
    $data[$id_spanduk] = [
      'id_spanduk' => $row['id_spanduk'],
      'judul_kegiatan' => $row['judul_kegiatan'],
      'tanggal_pasang' => $row['tanggal_pasang'],
      'lokasi' => []
    ];
  }
  if ($row['id_lokasi']) {
    $id_lokasi = $row['id_lokasi'];
    if (!isset($data[$id_spanduk]['lokasi'][$id_lokasi])) {
      $data[$id_spanduk]['lokasi'][$id_lokasi] = [
        'lokasi_pasang' => $row['lokasi_pasang'],
        'foto' => []
      ];
    }
    if ($row['id_foto']) {
      $data[$id_spanduk]['lokasi'][$id_lokasi]['foto'][] = [
        'file_path' => $row['file_path'],
        'keterangan' => $row['keterangan']
      ];
    }
  }
}
?>
<div class="card shadow mb-4">
  <div class="card-header py-3 d-flex justify-content-between align-items-center">
    <h6 class="m-0 font-weight-bold text-primary">Data Spanduk</h6>
  </div>
  <div class="card-body">
    <div class="mb-3">
      <a href="?page=i_add_spanduk" class="btn btn-primary">
        <i class="fa fa-edit"></i> Tambah Data
      </a>
    </div>
    <div class="table-responsive">
      <table class="table table-bordered text-gray-800" id="dataTable" width="100%" cellspacing="0">
        <thead>
          <tr>
            <th style="width:60px" class="text-center">No</th>
            <th style="width:100px" class="text-center">Tanggal</th>
            <th>Judul Kegiatan</th>
            <th style="width:200px">Lokasi</th>
            <th style="width:150px" class="text-center">Foto</th>
            <th style="width:120px" class="text-center">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($data)): ?>
            <tr>
              <td colspan="6" class="text-center text-muted">Belum ada data spanduk.</td>
            </tr>
            <?php else: $no = 1;
            foreach ($data as $spanduk): ?>
              <tr>
                <td class="text-center align-middle"><?= $no++ ?></td>
                <td class="text-center align-middle"><?= date('d/M/Y', strtotime($spanduk['tanggal_pasang'])) ?></td>
                <td class="align-middle font-weight-bold"><?= esc($spanduk['judul_kegiatan']) ?></td>
                <td class="align-middle">
                  <?php foreach ($spanduk['lokasi'] as $lok): ?>
                    <div>- <?= esc($lok['lokasi_pasang']) ?></div>
                  <?php endforeach; ?>
                </td>
                <td class="text-center align-middle">
                  <?php
                  $printed = false;
                  foreach ($spanduk['lokasi'] as $lok) {
                    foreach ($lok['foto'] as $foto) {
                      $fotoUrl = $foto['file_path'];
                  ?>
                      <a href="<?= esc($fotoUrl) ?>" target="_blank" rel="noopener" class="d-inline-block m-1">
                        <img src="<?= esc($fotoUrl) ?>" alt="Foto Spanduk" loading="lazy"
                          style="height:60px; object-fit:cover; border-radius:6px;">
                      </a>
                    <?php
                      $printed = true;
                    }
                  }
                  if (!$printed): ?>
                    <span class="text-muted small">Tidak ada</span>
                  <?php endif; ?>
                </td>
                <td class="text-center align-middle text-nowrap">
                  <a href="?page=i_detail_spanduk&id=<?= $spanduk['id_spanduk'] ?>"
                    class="btn btn-info btn-circle btn-sm" title="Detail">
                    <i class="fa fa-eye"></i>
                  </a>
                  <a href="?page=i_edit_spanduk&id=<?= $spanduk['id_spanduk'] ?>"
                    class="btn btn-success btn-circle btn-sm" title="Ubah">
                    <i class="fa fa-pencil-alt"></i>
                  </a>
                  <a href="?page=i_del_spanduk&id=<?= $spanduk['id_spanduk'] ?>"
                    class="btn btn-danger btn-circle btn-sm"
                    onclick="return confirm('Hapus spanduk ini?')" title="Hapus">
                    <i class="fa fa-trash"></i>
                  </a>
                </td>
              </tr>
          <?php endforeach;
          endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>