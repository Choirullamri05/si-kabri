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

if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));

$id   = (int)($_GET['id']   ?? 0);
$csrf = $_GET['csrf'] ?? '';

if ($id <= 0 || !hash_equals($_SESSION['csrf'], $csrf)) {
  $_SESSION['flash'] = ['type'=>'error','title'=>'Gagal','msg'=>'Request tidak valid.'];
  echo "<script>location='?page=i_data_iklan'</script>"; exit;
}

/* ==== Ambil data lama ==== */
$stmt = $koneksi->prepare("SELECT foto_iklan FROM iklan_koran WHERE id_iklan=?");
$stmt->bind_param('i', $id);
$stmt->execute();
$res  = $stmt->get_result();
$row  = $res->fetch_assoc();
$stmt->close();

if (!$row) {
  $_SESSION['flash'] = ['type'=>'error','title'=>'Gagal','msg'=>'Data tidak ditemukan.'];
  echo "<script>location='?page=i_data_iklan'</script>"; exit;
}

$fotoRel = trim((string)$row['foto_iklan']);

/* ==== Hapus record ==== */
$stmt = $koneksi->prepare("DELETE FROM iklan_koran WHERE id_iklan=?");
$stmt->bind_param('i', $id);
if ($stmt->execute()) {
  // Hapus file foto jika ada
  if ($fotoRel !== '') {
    $fs = $APP_ROOT . '/' . ltrim($fotoRel, '/');
    if (is_file($fs)) @unlink($fs);
  }
  $_SESSION['flash'] = ['type'=>'success','title'=>'Berhasil','msg'=>'Data berhasil dihapus.'];
} else {
  $_SESSION['flash'] = ['type'=>'error','title'=>'Gagal','msg'=>'Gagal menghapus: '.$stmt->error];
}
$stmt->close();

echo "<script>location='?page=i_data_iklan'</script>"; exit;
