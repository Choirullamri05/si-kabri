<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['ses_id'])) {
  echo "<script>location='login.php'</script>";
  exit;
}

if (!function_exists('app_paths')) {
  function app_paths() {
    $appRoot = realpath(__DIR__ . '/../../..');
    $docRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
    $appBase = '';
    if ($appRoot && $docRoot && str_starts_with($appRoot, $docRoot)) {
      $appBase = str_replace('\\', '/', substr($appRoot, strlen($docRoot)));
      $appBase = '/' . ltrim($appBase, '/');
      if ($appBase === '/') $appBase = '';
    }
    return [$appRoot, $appBase];
  }
}
[$APP_ROOT, $APP_BASE] = app_paths();

if (!function_exists('esc')) {
  function esc($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
}
if (!function_exists('foto_url')) {
  function foto_url($p){ global $APP_BASE; $p=trim((string)$p); if($p==='')return''; if(preg_match('#^https?://#i',$p))return $p; return rtrim($APP_BASE,'/').'/'.ltrim($p,'/'); }
}
if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
$csrf = $_SESSION['csrf'];

$kategoriOptions = [];
$qEnum = $koneksi->query("
  SELECT COLUMN_TYPE
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'iklan_koran'
    AND COLUMN_NAME = 'kategori_iklan'
  LIMIT 1
");
if ($qEnum && $row = $qEnum->fetch_assoc()) {
  if (preg_match("/^enum\\((.+)\\)$/i", $row['COLUMN_TYPE'], $m)) {
    $raw = $m[1];
    $kategoriOptions = array_map(fn($v)=>trim(trim($v),"'\""), explode(',', $raw));
  }
  $qEnum->free();
}

/* ==== Inisialisasi ==== */
$err = [];
$judul = $media = $tgl = $kategori = '';
$fotoPathForDB = ''; // disimpan web-relative

if ($_SERVER['REQUEST_METHOD']==='POST') {
  if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) $err[]='CSRF tidak valid.';
  $judul    = trim($_POST['judul_iklan'] ?? '');
  $media    = trim($_POST['nama_media'] ?? '');
  $tgl      = trim($_POST['tanggal_terbit'] ?? '');
  $kategori = trim($_POST['kategori_iklan'] ?? '');

  if ($judul==='')    $err[]='Judul wajib diisi.';
  if ($media==='')    $err[]='Nama media wajib diisi.';
  if ($tgl==='')      $err[]='Tanggal terbit wajib diisi.';
  if ($kategori==='') $err[]='Kategori wajib dipilih.';

  // ==== Validasi & proses upload ====
  if (empty($_FILES['foto_iklan']['name'])) {
    $err[] = 'Foto iklan wajib diunggah.';
  } else {
    $f = $_FILES['foto_iklan'];
    if ($f['error'] !== UPLOAD_ERR_OK) {
      $err[] = 'Gagal upload (error code '.$f['error'].').';
    } else {
      // Validasi mime & ekstensi
      $allowedExt = ['jpg','jpeg','png','webp'];
      $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
      if (!in_array($ext, $allowedExt, true)) {
        $err[] = 'Ekstensi harus jpg/jpeg/png/webp.';
      } else {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($f['tmp_name']) ?: '';
        $allowedMime = ['image/jpeg','image/png','image/webp'];
        if (!in_array($mime, $allowedMime, true)) {
          $err[] = 'File harus berupa gambar (JPEG/PNG/WEBP).';
        }
      }

      // Siapkan folder tujuan
      if (!$err) {
        $y = date('Y'); $m = date('m');
        $relDir  = "uploads/iklan_koran/$y/$m/";            // <= disimpan ke DB (web-relative)
        $destDir = $APP_ROOT . '/' . $relDir;               // path fisik

        if (!is_dir($destDir)) {
          if (!mkdir($destDir, 0775, true) && !is_dir($destDir)) {
            $err[] = 'Gagal membuat folder upload.';
          }
        }

        // Nama file aman & unik
        if (!$err) {
          $base = preg_replace('/[^a-z0-9-_]+/i','-', pathinfo($f['name'], PATHINFO_FILENAME));
          $base = trim($base,'-') ?: 'foto';
          $uniq = bin2hex(random_bytes(4));
          $safeFile = $base . '-' . $uniq . '.' . $ext;

          $destPathFs = $destDir . $safeFile;              // fisik
          $fotoPathForDB = $relDir . $safeFile;            // DB (web-relative)
          if (!move_uploaded_file($f['tmp_name'], $destPathFs)) {
            $err[] = 'Gagal memindahkan file upload.';
          } else {
            // Opsi: set permission file
            @chmod($destPathFs, 0644);
          }
        }
      }
    }
  }

  if (!$err) {
    $sql = "INSERT INTO iklan_koran (judul_iklan,nama_media,tanggal_terbit,kategori_iklan,foto_iklan,created_at)
            VALUES (?,?,?,?,?, NOW())";
    $stmt = $koneksi->prepare($sql);
    if (!$stmt) {
      $err[] = 'Query gagal disiapkan: '.$koneksi->error;
    } else {
      $stmt->bind_param('sssss', $judul, $media, $tgl, $kategori, $fotoPathForDB);
      if ($stmt->execute()) {
        $_SESSION['flash'] = ['type'=>'success','title'=>'Berhasil','msg'=>'Data tersimpan.'];
        echo "<script>location='?page=i_data_iklan'</script>"; exit;
      } else {
        $err[] = 'Gagal menyimpan: '.$stmt->error;
        // rollback file jika mau
      }
      $stmt->close();
    }
  }
}
?>
<div class="card shadow mb-4">
  <div class="card-header py-3 d-flex justify-content-between align-items-center">
    <h6 class="m-0 font-weight-bold text-primary">Tambah Iklan Koran</h6>
    <a href="?page=i_data_iklan" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Kembali</a>
  </div>
  <div class="card-body">
    <?php if ($err): ?>
      <div class="alert alert-danger">
        <ul class="mb-0">
          <?php foreach ($err as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>
    <form method="post" enctype="multipart/form-data">
      <input type="hidden" name="csrf" value="<?= esc($csrf) ?>">

      <div class="form-group">
        <label>Judul Iklan</label>
        <input type="text" name="judul_iklan" class="form-control" value="<?= esc($judul) ?>" required>
      </div>

      <div class="form-group">
        <label>Tanggal Terbit</label>
        <input type="date" name="tanggal_terbit" class="form-control" value="<?= esc($tgl) ?>" required>
      </div>

      <div class="form-group">
        <label>Kategori</label>
        <select name="kategori_iklan" class="form-control" required>
          <option value="">-- Pilih --</option>
          <?php foreach ($kategoriOptions as $opt): ?>
            <option value="<?= esc($opt) ?>" <?= ($opt===$kategori?'selected':'') ?>><?= esc($opt) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label>Nama Media</label>
        <input type="text" name="nama_media" class="form-control" value="<?= esc($media) ?>" required>
      </div>

      <div class="form-group">
        <label>Foto Iklan (jpg/png/webp)</label>
        <input type="file" name="foto_iklan" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="form-control-file" required>
        <small class="text-muted d-block mt-1">Maks ~5MB (tergantung config). Disimpan ke: <code>uploads/iklan_koran/YYYY/MM/</code></small>
      </div>

      <button class="btn btn-primary"><i class="fa fa-save"></i> Simpan</button>
      <a href="?page=i_data_iklan" class="btn btn-secondary">Batal</a>
    </form>
  </div>
</div>
