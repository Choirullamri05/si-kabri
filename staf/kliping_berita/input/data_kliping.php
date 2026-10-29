<?php
// Hitung total semua berita
$qTotal   = $koneksi->query("SELECT COUNT(*) AS jml FROM kliping_berita");
$total    = (int)($qTotal->fetch_assoc()['jml'] ?? 0);

// Hitung berita bulan ini
$qBulan   = $koneksi->query("
  SELECT COUNT(*) AS jml
  FROM kliping_berita
  WHERE YEAR(tgl_input)=YEAR(CURDATE()) AND MONTH(tgl_input)=MONTH(CURDATE())
");
$bulanIni = (int)($qBulan->fetch_assoc()['jml'] ?? 0);

// Hitung sentimen
$qPos = $koneksi->query("SELECT COUNT(*) AS jml FROM kliping_berita WHERE sentimen='Positif'");
$totPos = (int)($qPos->fetch_assoc()['jml'] ?? 0);

$qNeg = $koneksi->query("SELECT COUNT(*) AS jml FROM kliping_berita WHERE sentimen='Negatif'");
$totNeg = (int)($qNeg->fetch_assoc()['jml'] ?? 0);

// Hitung Prioritas / Non Prioritas
$totPrioritas = 0;
$totNon       = 0;
$sql = "SELECT kategori_program, COUNT(*) AS jml
        FROM kliping_berita
        GROUP BY kategori_program";
$res = $koneksi->query($sql);
if ($res) {
  while ($row = $res->fetch_assoc()) {
    if ($row['kategori_program'] === 'Prioritas')     $totPrioritas = (int)$row['jml'];
    if ($row['kategori_program'] === 'Non Prioritas') $totNon       = (int)$row['jml'];
  }
}
?>

<!-- ======== ALERT RINGKASAN ======== -->
<div class="alert alert-success alert-dismissible fade show" role="alert">
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
  <div class="d-flex align-items-center">
    <div>
      <h4 class="alert-heading mb-1"><i class="fas fa-info-circle"></i> Kliping Berita</h4>
      <p class="mb-0">Entri bulan ini:</p>
      <h5 class="text-success font-weight-bold"><?= esc($bulanIni) ?></h5>
      <p class="mb-0">Total seluruh kliping:</p>
      <h5 class="font-weight-bold"><?= esc($total) ?></h5>
      <p class="mb-0 mt-2">Ringkasan Sentimen:
        <span class="badge badge-success">Positif: <?= esc($totPos) ?></span>
        <span class="badge badge-danger">Negatif: <?= esc($totNeg) ?></span>
      </p>
      <p class="mb-0 mt-2">Ringkasan Kategori:
        <span class="badge badge-info">Prioritas: <?= esc($totPrioritas) ?></span>
        <span class="badge badge-secondary">Non Prioritas: <?= esc($totNon) ?></span>
      </p>
    </div>
  </div>
</div>

<!-- ======== TABEL DATA ======== -->
<div class="card shadow mb-4">
  <div class="card-header py-3 d-flex justify-content-between align-items-center">
    <h6 class="m-0 font-weight-bold text-primary">Data Kliping Berita</h6>
  </div>
  <div class="card-body">
    <div class="mb-3">
      <a href="?page=i_add_klp" class="btn btn-primary">
        <i class="fa fa-edit"></i> Tambah Data
      </a>
    </div>

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
            <th style="width:50px">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $sql = $koneksi->query("
            SELECT
              id_kliping,
              tgl_input,
              nama_media,
              judul_berita,
              sentimen,
              kategori_program
            FROM kliping_berita
            ORDER BY tgl_input DESC, id_kliping DESC
          ");

          $no = 1;
          if ($sql && $sql->num_rows > 0):
            while ($row = $sql->fetch_assoc()):
              $id      = (int)$row['id_kliping'];
              $tgl     = $row['tgl_input'] ? date("d/M/Y", strtotime($row['tgl_input'])) : '-';
              $media   = esc($row['nama_media'] ?? '');
              $judul   = esc($row['judul_berita'] ?? '');
              $sent    = $row['sentimen'] ?? '';
              $kat     = $row['kategori_program'] ?? '';

              // badge warna
              $badgeSent = $sent === 'Positif' ? 'badge-success' : ($sent === 'Negatif' ? 'badge-danger' : 'badge-secondary');
              $badgeKat  = $kat === 'Prioritas' ? 'badge-info' : 'badge-secondary';
          ?>
              <tr>
                <td><?= $no++; ?></td>
                <td><?= $tgl; ?></td>
                <td><?= $media; ?></td>
                <td><?= $judul; ?></td>
                <td><span class="badge <?= $badgeSent; ?>"><?= esc($sent); ?></span></td>
                <td><span class="badge <?= $badgeKat; ?>"><?= esc($kat); ?></span></td>
                <td class="text-nowrap">
                  <a href="?page=i_edit_klp&kode=<?= $id; ?>" title="Ubah" class="btn btn-success btn-circle btn-sm">
                    <i class="bi bi-pencil-fill"></i>
                  </a>
                  <a href="?page=i_del_klp&kode=<?= $id; ?>"
                    onclick="return confirm('Apakah anda yakin hapus data ini ?')"
                    title="Hapus" class="btn btn-danger btn-circle btn-sm">
                    <i class="fa fa-trash"></i>
                  </a>
                </td>
              </tr>
            <?php
            endwhile;
          else:
            ?>
            <tr>
              <td colspan="7" class="text-center">Belum ada data kliping.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>