<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['ses_id'])) {
  echo "<script>location='login.php'</script>";
  exit;
}

/* ==== APP PATHS ==== */
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

/* ==== Ambil ID ==== */
$id = (int)($_GET['id'] ?? 0);
if ($id<=0) { echo "<script>location='?page=i_data_iklan'</script>"; exit; }

/* ==== Ambil data lama ==== */
$old = null;
$stmt = $koneksi->prepare("SELECT id_iklan, judul_iklan, nama_media, tanggal_terbit, kategori_iklan, foto_iklan FROM iklan_koran WHERE id_iklan=?");
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
$old = $res->fetch_assoc();
$stmt->close();

if (!$old) { echo "<script>alert('Data tidak ditemukan');location='?page=i_data_iklan'</script>"; exit; }

/* ==== ENUM kategori ==== */
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

/* ==== Init form ==== */
$err = [];
$judul    = $old['judul_iklan'];
$media    = $old['nama_media'];
$tgl      = $old['tanggal_terbit'];
$kategori = $old['kategori_iklan'];
$fotoLama = $old['foto_iklan']; // web-relative
$fotoPreviewUrl = foto_url($fotoLama);

if ($_SERVER['REQUEST_METHOD']==='POST') {
  if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) $err[]='CSRF tidak valid.';
  $judul    = trim($_POST['judul_iklan'] ?? '');
  $media    = trim($_POST['nama_media'] ?? '');
  $tgl      = trim($_POST['tanggal_terbit'] ?? '');
  $kategori = trim($_POST['kategori_iklan'] ?? '');
  $hapusFoto= isset($_POST['hapus_foto']) ? true : false;

  if ($judul==='')    $err[]='Judul wajib diisi.';
  if ($media==='')    $err[]='Nama media wajib diisi.';
  if ($tgl==='')      $err[]='Tanggal terbit wajib diisi.';
  if ($kategori==='') $err[]='Kategori wajib dipilih.';

  $fotoPathForDB = $fotoLama; // default: tetap pakai lama

  // Hapus foto lama jika dicentang
  if ($hapusFoto && $fotoLama) {
    $fs = $APP_ROOT . '/' . ltrim($fotoLama,'/');
    if (is_file($fs)) @unlink($fs);
    $fotoPathForDB = '';
  }

  // Upload foto baru?
  if (!empty($_FILES['foto_iklan']['name'])) {
    $f = $_FILES['foto_iklan'];
    if ($f['error'] !== UPLOAD_ERR_OK) {
      $err[] = 'Gagal upload (error code '.$f['error'].').';
    } else {
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

      if (!$err) {
        $y = date('Y'); $m = date('m');
        $relDir  = "uploads/iklan_koran/$y/$m/";
        $destDir = $APP_ROOT . '/' . $relDir;
        if (!is_dir($destDir)) {
          if (!mkdir($destDir, 0775, true) && !is_dir($destDir)) {
            $err[]='Gagal membuat folder upload.';
          }
        }
        if (!$err) {
          $base = preg_replace('/[^a-z0-9-_]+/i','-', pathinfo($f['name'], PATHINFO_FILENAME));
          $base = trim($base,'-') ?: 'foto';
          $uniq = bin2hex(random_bytes(4));
          $safeFile   = $base.'-'.$uniq.'.'.$ext;
          $destPathFs = $destDir.$safeFile;
          $fotoBaruRel= $relDir.$safeFile;

          if (!move_uploaded_file($f['tmp_name'], $destPathFs)) {
            $err[]='Gagal memindahkan file upload.';
          } else {
            @chmod($destPathFs, 0644);
            // Hapus lama jika ada dan tidak sedang dihapus manual
            if ($fotoLama) {
              $oldFs = $APP_ROOT . '/' . ltrim($fotoLama,'/');
              if (is_file($oldFs)) @unlink($oldFs);
            }
            $fotoPathForDB = $fotoBaruRel;
          }
        }
      }
    }
  }

  if (!$err) {
    $sql = "UPDATE iklan_koran
               SET judul_iklan=?, nama_media=?, tanggal_terbit=?, kategori_iklan=?, foto_iklan=?
             WHERE id_iklan=?";
    $stmt = $koneksi->prepare($sql);
    if (!$stmt) {
      $err[] = 'Query gagal disiapkan: '.$koneksi->error;
    } else {
      $stmt->bind_param('sssssi', $judul, $media, $tgl, $kategori, $fotoPathForDB, $id);
      if ($stmt->execute()) {
        $_SESSION['flash']=['type'=>'success','title'=>'Berhasil','msg'=>'Perubahan disimpan.'];
        echo "<script>location='?page=i_data_iklan'</script>"; exit;
      } else {
        $err[]='Gagal menyimpan: '.$stmt->error;
      }
      $stmt->close();
    }
  }
  $fotoPreviewUrl = $fotoPathForDB ? foto_url($fotoPathForDB) : '';
}
?>
<div class="card shadow mb-4">
  <div class="card-header py-3 d-flex justify-content-between align-items-center">
    <h6 class="m-0 font-weight-bold text-primary">Ubah Iklan Koran</h6>
    <a href="?page=i_data_iklan" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Kembali</a>
  </div>
  <div class="card-body">
    <?php if ($err): ?>
      <div class="alert alert-danger">
        <ul class="mb-0"><?php foreach ($err as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?></ul>
      </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
      <input type="hidden" name="csrf" value="<?= esc($csrf) ?>">
      <div class="form-row">
        <div class="form-group col-md-6">
          <label>Judul Iklan</label>
          <input type="text" name="judul_iklan" class="form-control" value="<?= esc($judul) ?>" required>
        </div>
        <div class="form-group col-md-3">
          <label>Tanggal Terbit</label>
          <input type="date" name="tanggal_terbit" class="form-control" value="<?= esc($tgl) ?>" required>
        </div>
        <div class="form-group col-md-3">
          <label>Kategori</label>
          <select name="kategori_iklan" class="form-control" required>
            <?php foreach ($kategoriOptions as $opt): ?>
              <option value="<?= esc($opt) ?>" <?= ($opt===$kategori?'selected':'') ?>><?= esc($opt) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group col-md-6">
          <label>Nama Media</label>
          <input type="text" name="nama_media" class="form-control" value="<?= esc($media) ?>" required>
        </div>
        <div class="form-group col-md-6">
          <label>Foto Iklan (jpg/png/webp)</label>
          <?php if ($fotoPreviewUrl): ?>
            <div class="mb-2">
              <a href="<?= esc($fotoPreviewUrl) ?>" target="_blank"><img src="<?= esc($fotoPreviewUrl) ?>" style="height:80px;object-fit:cover;border-radius:6px"></a>
            </div>
          <?php endif; ?>
          <input type="file" name="foto_iklan" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="form-control-file">
          <div class="form-check mt-2">
            <input class="form-check-input" type="checkbox" name="hapus_foto" id="hapus_foto">
            <label class="form-check-label" for="hapus_foto">Hapus foto</label>
          </div>
        </div>
      </div>

      <button class="btn btn-primary"><i class="fa fa-save"></i> Simpan</button>
      <a href="?page=i_data_iklan" class="btn btn-secondary">Batal</a>
    </form>
  </div>
</div>
