<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// Validasi parameter
if (!isset($_GET['kode']) || !ctype_digit($_GET['kode'])) {
  echo "<script>
          Swal.fire({
            title:'Parameter tidak valid',
            icon:'warning'
          }).then(()=>{location='index.php?page=i_data_klp'})
        </script>";
  exit;
}

$id = (int)$_GET['kode'];

$koneksi->begin_transaction();
try {
  // Hapus langsung dari tabel kliping_berita saja
  $stmt = $koneksi->prepare("DELETE FROM kliping_berita WHERE id_kliping=?");
  $stmt->bind_param('i', $id);
  $stmt->execute();

  if ($stmt->affected_rows < 1) {
    throw new Exception("Data tidak ditemukan atau sudah dihapus.");
  }

  $stmt->close();
  $koneksi->commit();

  echo "<script>
          Swal.fire({
            title:'Hapus Data Berhasil',
            icon:'success'
          }).then(()=>{location='index.php?page=i_data_klp'})
        </script>";
} catch (Throwable $e) {
  $koneksi->rollback();
  echo "<script>
          Swal.fire({
            title:'Hapus Data Gagal',
            html:'" . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "',
            icon:'error'
          })
        </script>";
}
?>
