<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['ses_id'])) {
  echo "<script>location='login.php'</script>";
  exit;
}

if (!isset($_GET['id'])) {
  echo "<script>alert('Data tidak ditemukan'); window.location='?page=data_spanduk';</script>";
  exit;
}
$id_spanduk = (int)$_GET['id'];

$sp = $koneksi->query("SELECT * FROM spanduk WHERE id_spanduk=$id_spanduk")->fetch_assoc();
if (!$sp) {
  echo "<script>alert('Spanduk tidak ada'); window.location='?page=data_spanduk';</script>";
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $judul   = $_POST['judul_kegiatan'];
  $tanggal = $_POST['tanggal_pasang'];

  $stmt = $koneksi->prepare("UPDATE spanduk SET judul_kegiatan=?, tanggal_pasang=? WHERE id_spanduk=?");
  $stmt->bind_param("ssi", $judul, $tanggal, $id_spanduk);
  $stmt->execute();

  if (!empty($_POST['lokasi_update'])) {
    foreach ($_POST['lokasi_update'] as $id_lokasi => $nama_lokasi) {
      $stmt = $koneksi->prepare("UPDATE spanduk_lokasi SET lokasi_pasang=? WHERE id_lokasi=?");
      $stmt->bind_param("si", $nama_lokasi, $id_lokasi);
      $stmt->execute();
    }
  }

  // update foto lama
  if (!empty($_POST['foto_lama'])) {
    foreach ($_POST['foto_lama'] as $id_foto => $ket) {
      // kalau ada file baru
      if (!empty($_FILES['foto_baru']['name'][$id_foto])) {
        $uploadDir = "uploads/spanduk/".date("Y/m")."/";
        if (!is_dir($uploadDir)) mkdir($uploadDir,0777,true);

        $fileName = time()."_".basename($_FILES['foto_baru']['name'][$id_foto]);
        $target   = $uploadDir.$fileName;
        move_uploaded_file($_FILES['foto_baru']['tmp_name'][$id_foto], $target);

        // hapus file lama
        $lama = $koneksi->query("SELECT file_path FROM spanduk_foto WHERE id_foto=$id_foto")->fetch_assoc();
        if ($lama && file_exists($lama['file_path'])) unlink($lama['file_path']);

        $stmt = $koneksi->prepare("UPDATE spanduk_foto SET file_path=?, keterangan=? WHERE id_foto=?");
        $stmt->bind_param("ssi", $target, $ket, $id_foto);
        $stmt->execute();
      } else {
        // hanya update keterangan
        $stmt = $koneksi->prepare("UPDATE spanduk_foto SET keterangan=? WHERE id_foto=?");
        $stmt->bind_param("si", $ket, $id_foto);
        $stmt->execute();
      }
    }
  }

  // tambah foto baru ke lokasi lama
  if (!empty($_POST['lokasi_tujuan'])) {
    foreach ($_POST['lokasi_tujuan'] as $key => $id_lokasi) {
      if (!empty($_FILES['foto']['name'][$key])) {
        $uploadDir = "uploads/spanduk/".date("Y/m")."/";
        if (!is_dir($uploadDir)) mkdir($uploadDir,0777,true);

        $fileName = time()."_".basename($_FILES['foto']['name'][$key]);
        $target   = $uploadDir.$fileName;
        move_uploaded_file($_FILES['foto']['tmp_name'][$key], $target);

        $ket = $_POST['keterangan'][$key] ?? '';
        $stmt = $koneksi->prepare("INSERT INTO spanduk_foto (id_lokasi, file_path, keterangan) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $id_lokasi, $target, $ket);
        $stmt->execute();
      }
    }
  }

  // tambah lokasi baru
  if (!empty($_POST['lokasi_baru'])) {
    $lokasiBaru = $_POST['lokasi_baru'];
    $stmt = $koneksi->prepare("INSERT INTO spanduk_lokasi (id_spanduk, lokasi_pasang) VALUES (?, ?)");
    $stmt->bind_param("is", $id_spanduk, $lokasiBaru);
    $stmt->execute();
    $id_lokasi_baru = $stmt->insert_id;

    // tambah foto untuk lokasi baru
    if (!empty($_FILES['foto_baru_lokasi']['name'][0])) {
      $uploadDir = "uploads/spanduk/".date("Y/m")."/";
      if (!is_dir($uploadDir)) mkdir($uploadDir,0777,true);

      foreach ($_FILES['foto_baru_lokasi']['tmp_name'] as $key => $tmp) {
        if ($_FILES['foto_baru_lokasi']['error'][$key] == UPLOAD_ERR_OK) {
          $fileName = time()."_".basename($_FILES['foto_baru_lokasi']['name'][$key]);
          $target   = $uploadDir.$fileName;
          move_uploaded_file($tmp, $target);

          $ket = $_POST['ket_baru_lokasi'][$key] ?? '';
          $stmt = $koneksi->prepare("INSERT INTO spanduk_foto (id_lokasi, file_path, keterangan) VALUES (?, ?, ?)");
          $stmt->bind_param("iss", $id_lokasi_baru, $target, $ket);
          $stmt->execute();
        }
      }
    }
  }

  echo "<script>Swal.fire({title:'Ubah Data Berhasil',icon:'success'}).then(()=>{location='index.php?page=i_data_spanduk'})</script>";
}

// ambil lokasi lama
$lokasi_res = $koneksi->query("SELECT * FROM spanduk_lokasi WHERE id_spanduk=$id_spanduk");
?>

<div class="card shadow mb-4">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h6 class="m-0 font-weight-bold text-primary">Edit Spanduk</h6>
    <a href="?page=i_data_spanduk&id=<?= $id_spanduk ?>" class="btn btn-secondary btn-sm">
      <i class="fa fa-arrow-left"></i> Kembali
    </a>
  </div>
  <div class="card-body">
    <form method="post" enctype="multipart/form-data">
      <div class="form-group">
        <label>Judul Kegiatan</label>
        <input type="text" name="judul_kegiatan" 
               value="<?= htmlspecialchars($sp['judul_kegiatan']) ?>" 
               class="form-control" required>
      </div>
      <div class="form-group">
        <label>Tanggal Pasang</label>
        <input type="date" name="tanggal_pasang" 
               value="<?= $sp['tanggal_pasang'] ?>" 
               class="form-control" required>
      </div>

      <hr>
      <h6 class="font-weight-bold mb-3">Lokasi & Foto</h6>

      <?php while ($lok = $lokasi_res->fetch_assoc()): ?>
        <div class="card mb-3 border-primary">
          <div class="card-header bg-light d-flex justify-content-between">
            <input type="text" name="lokasi_update[<?= $lok['id_lokasi'] ?>]" 
                   value="<?= htmlspecialchars($lok['lokasi_pasang']) ?>" 
                   class="form-control">
          </div>
          <div class="card-body">
            <h6 class="mb-2">Foto Lama</h6>
            <?php
            $foto_res = $koneksi->query("SELECT * FROM spanduk_foto WHERE id_lokasi=" . $lok['id_lokasi']);
            if ($foto_res->num_rows == 0): ?>
              <em class="text-muted">Belum ada foto</em>
            <?php endif; ?>

            <?php while ($f = $foto_res->fetch_assoc()): ?>
              <div class="mb-2 d-flex align-items-center border rounded p-2">
                <img src="<?= $f['file_path'] ?>" class="img-thumbnail mr-2" style="height:80px;">
                <input type="text" name="foto_lama[<?= $f['id_foto'] ?>]" 
                       value="<?= htmlspecialchars($f['keterangan']) ?>" 
                       class="form-control mr-2" placeholder="Keterangan foto">
                <input type="file" name="foto_baru[<?= $f['id_foto'] ?>]" class="form-control-file">
                <a href="?page=delete_foto&id=<?= $f['id_foto'] ?>&spanduk=<?= $id_spanduk ?>" 
                   onclick="return confirm('Hapus foto ini?')" 
                   class="btn btn-danger btn-sm ml-2"><i class="fa fa-trash"></i></a>
              </div>
            <?php endwhile; ?>

            <hr>
            <h6 class="mb-2">Tambah Foto Baru</h6>
            <div id="foto-container-<?= $lok['id_lokasi'] ?>">
              <div class="input-group mb-2">
                <input type="file" name="foto[]" class="form-control">
                <input type="text" name="keterangan[]" class="form-control" placeholder="Keterangan (opsional)">
                <input type="hidden" name="lokasi_tujuan[]" value="<?= $lok['id_lokasi'] ?>">
              </div>
            </div>
            <button type="button" class="btn btn-success btn-sm" 
                    onclick="addFotoField(<?= $lok['id_lokasi'] ?>)">+ Tambah Foto</button>
          </div>
        </div>
      <?php endwhile; ?>
      <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
    </form>
  </div>
</div>

<script>
function addFotoField(lokasiId) {
  let container = document.getElementById('foto-container-' + lokasiId);
  let div = document.createElement('div');
  div.className = "input-group mb-2";
  div.innerHTML = `
    <input type="file" name="foto[]" class="form-control">
    <input type="text" name="keterangan[]" class="form-control" placeholder="Keterangan (opsional)">
    <input type="hidden" name="lokasi_tujuan[]" value="`+lokasiId+`">
    <button type="button" class="btn btn-danger" onclick="this.parentElement.remove()">Hapus</button>
  `;
  container.appendChild(div);
}
</script>
