<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['ses_id'])) {
  echo "<script>location='login.php'</script>";
  exit;
}

if (!isset($_GET['id'])) {
  echo "<script>alert('Data tidak ditemukan'); window.location='?page=i_data_spanduk';</script>";
  exit;
}

$id_spanduk = (int)$_GET['id'];

$sp = $koneksi->query("SELECT * FROM spanduk WHERE id_spanduk=$id_spanduk")->fetch_assoc();
if (!$sp) {
  echo "<script>alert('Spanduk tidak ada'); window.location='?page=i_data_spanduk';</script>";
  exit;
}

$lokasi_res = $koneksi->query("SELECT * FROM spanduk_lokasi WHERE id_spanduk=$id_spanduk ORDER BY id_lokasi ASC");
?>

<div class="card shadow mb-4">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h6 class="m-0 font-weight-bold text-primary">Detail Spanduk</h6>
    <a href="?page=lap_spanduk" class="btn btn-secondary btn-sm">
      <i class="fa fa-arrow-left"></i> Kembali
    </a>
  </div>
  <div class="card-body">
    <h5 class="font-weight-bold mb-1"><?= htmlspecialchars($sp['judul_kegiatan']) ?></h5>
    <p class="text-muted mb-3"><i class="fa fa-calendar"></i> <?= date('d M Y', strtotime($sp['tanggal_pasang'])) ?></p>
    <hr>

    <h6 class="font-weight-bold mb-2">Lokasi & Foto</h6>

    <?php if ($lokasi_res->num_rows == 0): ?>
      <div class="alert alert-warning">Belum ada lokasi terdaftar.</div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-bordered text-gray-800">
          <thead class="thead-dark">
            <tr>
              <th style="width:60px" class="text-center">No</th>
              <th style="width:250px">Lokasi</th>
              <th>Foto Spanduk</th>
            </tr>
          </thead>
          <tbody>
            <?php $no=1; while($lok=$lokasi_res->fetch_assoc()): ?>
              <tr>
                <td class="text-center align-middle"><?= $no++ ?></td>
                <td class="align-middle"><?= htmlspecialchars($lok['lokasi_pasang']) ?></td>
                <td>
                  <?php
                  $foto_res = $koneksi->query("SELECT * FROM spanduk_foto WHERE id_lokasi=".$lok['id_lokasi']);
                  if ($foto_res->num_rows == 0): ?>
                    <span class="text-muted small">Tidak ada foto</span>
                  <?php else: ?>
                    <div class="d-flex flex-wrap">
                      <?php while($f=$foto_res->fetch_assoc()): ?>
                        <div class="m-1 text-center">
                          <a href="<?= $f['file_path'] ?>" target="_blank" rel="noopener">
                            <img src="<?= $f['file_path'] ?>" 
                                 alt="foto spanduk" 
                                 class="img-thumbnail" style="height:80px; object-fit:cover;">
                          </a><br>
                          <small class="text-muted"><?= htmlspecialchars($f['keterangan']) ?></small>
                        </div>
                      <?php endwhile; ?>
                    </div>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>
