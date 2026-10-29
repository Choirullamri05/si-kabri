<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include "inc/koneksi.php";

if (isset($_GET['kode']) && ctype_digit($_GET['kode'])) {
    $id = (int) $_GET['kode'];

    // Hapus biodata terkait terlebih dahulu
    mysqli_query($koneksi, "DELETE FROM biodata WHERE id_pengguna = $id");

    // Hapus data pengguna
    $sql_hapus = "DELETE FROM pengguna WHERE id_pengguna = $id";
    $query_hapus = mysqli_query($koneksi, $sql_hapus);

    if ($query_hapus) {
        echo "<script>
        Swal.fire({
            title: 'Hapus Data Berhasil',
            icon: 'success',
            confirmButtonText: 'OK'
        }).then(() => {
            window.location='index.php?page=MyApp/data_pengguna';
        });
        </script>";
    } else {
        $err = addslashes(mysqli_error($koneksi));
        echo "<script>
        Swal.fire({
            title: 'Hapus Gagal',
            text: '$err',
            icon: 'error'
        }).then(() => {
            window.location='index.php?page=MyApp/data_pengguna';
        });
        </script>";
    }
}
?>
