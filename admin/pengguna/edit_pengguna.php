<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($koneksi)) {
  require_once __DIR__ . "/../../inc/koneksi.php";
}

// Validasi parameter GET
if (!isset($_GET['kode']) || !ctype_digit($_GET['kode'])) {
  echo '<div class="alert alert-warning">Parameter tidak valid.</div>';
  return;
}

$id = (int) $_GET['kode'];

// Ambil data pengguna
$stmt = $koneksi->prepare("
  SELECT 
    p.id_pengguna, p.username, p.level,
    b.nama_lengkap, b.jabatan
  FROM pengguna p
  LEFT JOIN biodata b ON b.id_pengguna = p.id_pengguna
  WHERE p.id_pengguna = ?
  LIMIT 1
");
$stmt->bind_param('i', $id);
$stmt->execute();
$cek = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$cek) {
  echo '<div class="alert alert-danger">User tidak ditemukan.</div>';
  return;
}

// ======================================
// PROSES SIMPAN
// ======================================
if (isset($_POST['Ubah'])) {

  $errors = [];   // <-- penting! dideklarasikan dari awal

  $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
  $jabatan      = trim($_POST['jabatan'] ?? '');
  $username     = trim($_POST['username'] ?? '');
  $level        = $_POST['level'] ?? '';
  $new_pass     = $_POST['password'] ?? '';

  // Validasi sederhana
  if ($nama_lengkap === '') $errors[] = "Nama lengkap wajib diisi.";
  if ($username === '')     $errors[] = "Username wajib diisi.";
  if (!in_array($level, ['Admin','Staf','Pimpinan'], true)) $errors[] = "Level tidak valid.";
  if ($new_pass !== '' && strlen($new_pass) < 5) $errors[] = "Password minimal 5 karakter.";

  // Cek duplikasi username
  $stmt = $koneksi->prepare("SELECT COUNT(*) AS jml FROM pengguna WHERE username=? AND id_pengguna<>?");
  $stmt->bind_param('si', $username, $id);
  $stmt->execute();
  $dupe = $stmt->get_result()->fetch_assoc()['jml'] ?? 0;
  $stmt->close();

  if ($dupe > 0) $errors[] = "Username sudah digunakan pengguna lain.";

  // Jika validasi gagal
  if (!empty($errors)) {
    echo "<script>
      Swal.fire({
        title:'Validasi gagal',
        html:'".implode('<br>', $errors)."',
        icon:'warning'
      });
    </script>";
  } else {

    $koneksi->begin_transaction();
    try {

      // Update table pengguna
      if ($new_pass !== '') {
        $hash = password_hash($new_pass, PASSWORD_DEFAULT);
        $stmt = $koneksi->prepare("UPDATE pengguna SET username=?, level=?, password_hash=? WHERE id_pengguna=?");
        $stmt->bind_param('sssi', $username, $level, $hash, $id);
      } else {
        $stmt = $koneksi->prepare("UPDATE pengguna SET username=?, level=? WHERE id_pengguna=?");
        $stmt->bind_param('ssi', $username, $level, $id);
      }
      $stmt->execute();
      $stmt->close();

      // Update / insert biodata
      $stmt = $koneksi->prepare("UPDATE biodata SET nama_lengkap=?, jabatan=? WHERE id_pengguna=?");
      $stmt->bind_param('ssi', $nama_lengkap, $jabatan, $id);
      $stmt->execute();

      if ($stmt->affected_rows === 0) {
        $stmt->close();
        $stmt = $koneksi->prepare("INSERT IGNORE INTO biodata (id_pengguna, nama_lengkap, jabatan) VALUES (?, ?, ?)");
        $stmt->bind_param('iss', $id, $nama_lengkap, $jabatan);
        $stmt->execute();
      }
      $stmt->close();

      $koneksi->commit();

      echo "<script>
      Swal.fire({
        title: 'Ubah Data Berhasil',
        icon: 'success',
        confirmButtonText: 'OK'
      }).then(()=>{ window.location='index.php?page=MyApp/data_pengguna'; });
      </script>";

    } catch (Throwable $e) {
      $koneksi->rollback();
      echo "<script>
      Swal.fire({
        title: 'Ubah Data Gagal',
        html: '".$e->getMessage()."',
        icon: 'error'
      });
      </script>";
    }
  }
}

$val_nama   = htmlspecialchars($cek['nama_lengkap'] ?? '', ENT_QUOTES, 'UTF-8');
$val_jabatan= htmlspecialchars($cek['jabatan'] ?? '', ENT_QUOTES, 'UTF-8');
$val_user   = htmlspecialchars($cek['username'] ?? '', ENT_QUOTES, 'UTF-8');
$val_level  = htmlspecialchars($cek['level'] ?? 'Staf', ENT_QUOTES, 'UTF-8');
?>


<div class="card card-success">
  <div class="card-header">
    <h3 class="card-title"><i class="fa fa-edit"></i> Ubah Data</h3>
  </div>
  <form action="" method="post">
    <div class="card-body">

      <input type="hidden" name="id_pengguna" value="<?= $id; ?>"/>

      <div class="form-group row">
        <label class="col-sm-2 col-form-label">Nama Lengkap</label>
        <div class="col-sm-6">
          <input type="text" class="form-control" name="nama_lengkap" value="<?= $val_nama; ?>" required/>
        </div>
      </div>

      <div class="form-group row">
        <label class="col-sm-2 col-form-label">Jabatan</label>
        <div class="col-sm-6">
          <input type="text" class="form-control" name="jabatan" value="<?= $val_jabatan; ?>"/>
        </div>
      </div>

      <div class="form-group row">
        <label class="col-sm-2 col-form-label">Username</label>
        <div class="col-sm-6">
          <input type="text" class="form-control" name="username" value="<?= $val_user; ?>" required/>
        </div>
      </div>

      <div class="form-group row">
        <label class="col-sm-2 col-form-label">Password Baru</label>
        <div class="col-sm-6">
          <input type="password" class="form-control" id="new_password" name="password" placeholder="Kosongkan jika tidak diganti"/>
          <div class="mt-1">
            <input id="togglePass" type="checkbox" class="form-checkbox" onclick="togglePassword()"> Tampilkan Password
          </div>
        </div>
      </div>

      <div class="form-group row">
        <label class="col-sm-2 col-form-label">Level</label>
        <div class="col-sm-4">
          <select name="level" class="form-control" required>
            <option value="">-- Pilih Level --</option>
            <option value="Admin"    <?= $val_level==='Admin'    ? 'selected':''; ?>>Admin</option>
            <option value="Staf"     <?= $val_level==='Staf'     ? 'selected':''; ?>>Staf</option>
            <option value="Pimpinan" <?= $val_level==='Pimpinan' ? 'selected':''; ?>>Pimpinan</option>
          </select>
        </div>
      </div>

    </div>
    <div class="card-footer">
      <input type="submit" name="Ubah" value="Simpan" class="btn btn-info">
      <a href="index.php?page=MyApp/data_pengguna" class="btn btn-secondary">Batal</a>
    </div>
  </form>
</div>

<script>
function togglePassword(){
  var el = document.getElementById('new_password');
  el.type = (el.type === 'password') ? 'text' : 'password';
}
</script>
