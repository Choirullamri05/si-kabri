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

$res = $koneksi->query("SELECT f.file_path FROM spanduk_foto f JOIN spanduk_lokasi l ON f.id_lokasi=l.id_lokasi WHERE l.id_spanduk=$id_spanduk");
while($r=$res->fetch_assoc()){
  if ($r['file_path'] && file_exists($r['file_path'])) {
    unlink($r['file_path']);
  }
}

$koneksi->query("DELETE FROM spanduk_foto WHERE id_lokasi IN (SELECT id_lokasi FROM spanduk_lokasi WHERE id_spanduk=$id_spanduk)");
$koneksi->query("DELETE FROM spanduk_lokasi WHERE id_spanduk=$id_spanduk");
$koneksi->query("DELETE FROM spanduk WHERE id_spanduk=$id_spanduk");

echo "<script>Swal.fire({title:'Hapus Data Berhasil',icon:'success'}).then(()=>{location='index.php?page=i_data_spanduk'})</script>";
