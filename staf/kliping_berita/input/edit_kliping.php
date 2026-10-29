<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// ===== Validasi parameter =====
if (!isset($_GET['kode']) || !ctype_digit($_GET['kode'])) {
  echo "<script>
          Swal.fire({title:'Parameter tidak valid',icon:'warning'})
          .then(()=>{location='index.php?page=i_data_klp'})
        </script>";
  exit;
}

$id = (int)$_GET['kode'];

// ===== Ambil data dari database =====
$stmt = $koneksi->prepare("SELECT * FROM kliping_berita WHERE id_kliping=? LIMIT 1");
$stmt->bind_param('i', $id);
$stmt->execute();
$kliping = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$kliping) {
  echo "<script>
          Swal.fire({title:'Data tidak ditemukan',icon:'error'})
          .then(()=>{location='index.php?page=i_data_klp'})
        </script>";
  exit;
}

/* ============================================================
   SIMPAN PERUBAHAN KATEGORI MANUAL (Form Modal)
   ============================================================ */
if (isset($_POST['SimpanKategori'])) {
  $kategoriBaru = $_POST['kategori_manual'] ?? '';
  $admin        = $_SESSION['ses_user'];

  $stmt = $koneksi->prepare("
    UPDATE kliping_berita
    SET kategori_program=?, verified_by=?, verified_at=NOW()
    WHERE id_kliping=?
  ");
  $stmt->bind_param('ssi', $kategoriBaru, $admin, $id);
  $stmt->execute();
  $stmt->close();

  echo "<script>
          Swal.fire({title:'Kategori berhasil diubah',icon:'success'})
          .then(()=>{location='index.php?page=i_data_klp'})
        </script>";
  exit;
}

/* ============================================================
   SIMPAN PERUBAHAN DATA UTAMA (EDIT TANPA MENGUBAH KATEGORI)
   ============================================================ */
if (isset($_POST['Simpan'])) {
  $tgl_input    = trim($_POST['tgl_input'] ?? '');
  $nama_media   = trim($_POST['nama_media'] ?? '');
  $judul_berita = trim($_POST['judul_berita'] ?? '');
  $sentimen     = $_POST['sentimen'] ?? '';
  $is_training  = isset($_POST['is_training']) ? 1 : 0;
  $admin        = $_SESSION['ses_user'];

  $errors = [];
  if ($nama_media === '') $errors[] = "Nama media wajib diisi.";
  if ($judul_berita === '') $errors[] = "Judul berita wajib diisi.";
  if (!in_array($sentimen, ['Positif','Negatif'], true)) $errors[] = "Sentimen tidak valid.";

  if (!$errors) {
    $stmt = $koneksi->prepare("
      UPDATE kliping_berita
      SET nama_media=?, judul_berita=?, sentimen=?, tgl_input=?,
          is_training=?, verified_by=?, verified_at=NOW()
      WHERE id_kliping=?
    ");
    $stmt->bind_param('ssssisi',
      $nama_media, $judul_berita, $sentimen, $tgl_input,
      $is_training, $admin, $id
    );
    $stmt->execute();
    $stmt->close();

    echo "<script>
            Swal.fire({
              title:'Data berhasil diperbarui',
              icon:'success'
            }).then(()=>{location='index.php?page=i_data_klp'})
          </script>";
    exit;
  }
}
?>

<!-- ========================================================
     FORM EDIT UTAMA
     ======================================================== -->
<div class="card card-success">
  <div class="card-header">
    <h3 class="card-title"><i class="fa fa-edit"></i> Ubah Kliping Berita</h3>
  </div>

  <form method="post">
    <div class="card-body">

      <div class="form-group row">
        <label class="col-sm-2 col-form-label">Tanggal</label>
        <div class="col-sm-4">
          <input type="date" class="form-control" name="tgl_input"
                 value="<?= htmlspecialchars($kliping['tgl_input']) ?>">
        </div>
      </div>

      <div class="form-group row">
        <label class="col-sm-2 col-form-label">Nama Media</label>
        <div class="col-sm-6">
          <input type="text" class="form-control" name="nama_media"
                 value="<?= htmlspecialchars($kliping['nama_media']) ?>" required>
        </div>
      </div>

      <div class="form-group row">
        <label class="col-sm-2 col-form-label">Judul Berita</label>
        <div class="col-sm-8">
          <textarea class="form-control" name="judul_berita" rows="3" required><?= htmlspecialchars($kliping['judul_berita']) ?></textarea>
        </div>
      </div>

      <div class="form-group row">
        <label class="col-sm-2 col-form-label">Sentimen</label>
        <div class="col-sm-4">
          <select name="sentimen" class="form-control">
            <option value="Positif" <?= ($kliping['sentimen']=='Positif')?'selected':'' ?>>Positif</option>
            <option value="Negatif" <?= ($kliping['sentimen']=='Negatif')?'selected':'' ?>>Negatif</option>
          </select>
        </div>
      </div>

      <!-- Tampilkan kategori & tombol ubah -->
      <div class="form-group row">
        <label class="col-sm-2 col-form-label">Kategori Sekarang</label>
        <div class="col-sm-4">
          <input type="text" class="form-control" value="<?= htmlspecialchars($kliping['kategori_program']) ?>" readonly>
        </div>
        <div class="col-sm-3">
          <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#modalKategori">
            <i class="fa fa-edit"></i> Ubah Kategori
          </button>
        </div>
      </div>

      <!-- Checkbox Data Latih -->
      <div class="form-group row">
        <label class="col-sm-2 col-form-label">Jadikan Data Latih</label>
        <div class="col-sm-4">
          <input type="checkbox" name="is_training" value="1">
          <small class="text-muted">Gunakan berita ini untuk training model ML tambahan.</small>
        </div>
      </div>

    </div>
    <div class="card-footer">
      <input type="submit" name="Simpan" value="Simpan Perubahan Data" class="btn btn-success">
      <a href="index.php?page=i_data_klp" class="btn btn-secondary">Batal</a>
    </div>
  </form>
</div>

<!-- ========================================================
     MODAL EDIT KATEGORI
     ======================================================== -->
<div class="modal fade" id="modalKategori" tabindex="-1">
  <div class="modal-dialog">
    <form method="post">
      <div class="modal-content">
        <div class="modal-header bg-warning">
          <h class="modal-title">Ubah Kategori Program</h>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>

        <div class="modal-body">
          <select name="kategori_manual" class="form-control">
            <option value="Prioritas">Prioritas</option>
            <option value="Non Prioritas">Non Prioritas</option>
          </select>
        </div>
        <div class="modal-footer">
          <button type="submit" name="SimpanKategori" class="btn btn-warning">Simpan</button>
        </div>
      </div>
    </form>
  </div>
</div>
