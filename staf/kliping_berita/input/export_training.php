<?php
include "../../../inc/koneksi.php";     

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=training_dataset.csv');

$output = fopen("php://output", "w");

// header CSV
fputcsv($output, ['judul_berita','label'], ',', '"', "\\");

$query = mysqli_query($koneksi, "
  SELECT judul_berita, kategori_program AS label
  FROM kliping_berita
  WHERE is_training = 1
  ORDER BY id_kliping DESC
");

while ($row = mysqli_fetch_assoc($query)) {
  fputcsv($output, [$row['judul_berita'], $row['label']], ',', '"', "\\");
}

fclose($output);
exit;
?>
