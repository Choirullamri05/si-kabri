<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$errors = [];
$success = "";

// Proses simpan
if (isset($_POST['Simpan'])) {
  $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
  $jabatan      = trim($_POST['jabatan'] ?? '');
  $username     = trim($_POST['username'] ?? '');
  $password     = $_POST['password'] ?? '';
  $level        = $_POST['level'] ?? '';

  // Validasi dasar
  if ($nama_lengkap === '') $errors[] = "Nama lengkap wajib diisi.";
  if ($username === '')     $errors[] = "Username wajib diisi.";
  if (strlen($password) < 5) $errors[] = "Password minimal 5 karakter.";
  if (!in_array($level, ['Admin','Staf'], true)) $errors[] = "Level tidak valid.";

  // Cek duplikasi username
  if (!$errors) {
    $stmt = $koneksi->prepare("SELECT 1 FROM pengguna WHERE username=? LIMIT 1");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) $errors[] = "Username sudah digunakan.";
    $stmt->close();
  }

  if (!$errors) {
    $koneksi->begin_transaction();
    try {
      // Insert ke pengguna
      $hash = password_hash($password, PASSWORD_DEFAULT);
      $stmt1 = $koneksi->prepare("INSERT INTO pengguna (username, password_hash, level, is_active) VALUES (?, ?, ?, 1)");
      $stmt1->bind_param('sss', $username, $hash, $level);
      $stmt1->execute();
      if ($stmt1->affected_rows < 1) throw new Exception("Gagal menyimpan pengguna.");
      $id_pengguna = $koneksi->insert_id;
      $stmt1->close();

      // Insert ke biodata
      $stmt2 = $koneksi->prepare("INSERT INTO biodata (id_pengguna, nama_lengkap, jabatan) VALUES (?, ?, ?)");
      $stmt2->bind_param('iss', $id_pengguna, $nama_lengkap, $jabatan);
      $stmt2->execute();
      if ($stmt2->affected_rows < 1) throw new Exception("Gagal menyimpan biodata.");
      $stmt2->close();

      $koneksi->commit();
      $success = "Tambah Data Berhasil";
      echo "<script>
        Swal.fire({title:'$success', icon:'success', confirmButtonText:'OK'})
        .then(()=>{ window.location='index.php?page=MyApp/data_pengguna'; });
      </script>";
      exit;
    } catch (Throwable $e) {
      $koneksi->rollback();
      $errors[] = $e->getMessage();
    }
  }

  if ($errors) {
    echo "<script>
      Swal.fire({title:'Tambah Data Gagal', html:'".esc(implode('<br>', $errors))."', icon:'error', confirmButtonText:'OK'});
    </script>";
  }
}
?>

<div class="card card-primary">
  <div class="card-header">
    <h3 class="card-title"><i class="fa fa-edit"></i> Tambah Data</h3>
  </div>
  <form action="" method="post">
    <div class="card-body">

      <div class="form-group row">
        <label class="col-sm-2 col-form-label">Nama Lengkap</label>
        <div class="col-sm-6">
          <input type="text" class="form-control" name="nama_lengkap" placeholder="Nama lengkap"
                 value="<?= esc($_POST['nama_lengkap'] ?? '') ?>" required>
        </div>
      </div>

      <div class="form-group row">
        <label class="col-sm-2 col-form-label">Jabatan</label>
        <div class="col-sm-6">
          <input type="text" class="form-control" name="jabatan" placeholder="Jabatan (opsional)"
                 value="<?= esc($_POST['jabatan'] ?? '') ?>">
        </div>
      </div>

      <div class="form-group row">
        <label class="col-sm-2 col-form-label">Username</label>
        <div class="col-sm-6">
          <input type="text" class="form-control" name="username" placeholder="Username"
                 value="<?= esc($_POST['username'] ?? '') ?>" required>
        </div>
      </div>

      <div class="form-group row">
        <label class="col-sm-2 col-form-label">Password</label>
        <div class="col-sm-6">
          <input type="password" class="form-control" id="pass" name="password" placeholder="Minimal 5 karakter" required>
          <div class="mt-1">
            <input id="showpass" type="checkbox" onclick="togglePass()"> Lihat Password
          </div>
        </div>
      </div>

      <div class="form-group row">
        <label class="col-sm-2 col-form-label">Level</label>
        <div class="col-sm-4">
          <select name="level" class="form-control" required>
            <option value="">- Pilih -</option>
            <option value="Admin"    <?= (($_POST['level'] ?? '')==='Admin')?'selected':''; ?>>Admin</option>
            <option value="Staf"     <?= (($_POST['level'] ?? '')==='Staf')?'selected':''; ?>>Staf</option>
          </select>
        </div>
      </div>

    </div>
    <div class="card-footer">
      <input type="submit" name="Simpan" value="Simpan" class="btn btn-info">
      <a href="index.php?page=MyApp/data_pengguna" class="btn btn-secondary">Batal</a>
    </div>
  </form>
</div>

<script>
function togglePass(){
  var el = document.getElementById('pass');
  el.type = (el.type === 'password') ? 'text' : 'password';
}
</script>
