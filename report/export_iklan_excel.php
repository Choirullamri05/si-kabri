<?php
include "../inc/koneksi.php";

/* Query sederhana tanpa filter dulu */
$sql = $koneksi->query("SELECT * FROM iklan_koran ORDER BY tanggal_terbit ASC");

/* Header Excel */
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Laporan_Iklan.xls");
header("Pragma: no-cache");
header("Expires: 0");

echo "<table border='1'>";
echo "<tr>
        <th>No</th>
        <th>Tanggal</th>
        <th>Media</th>
        <th>Judul Iklan</th>
        <th>Kategori</th>
      </tr>";

$no=1;
while ($row = $sql->fetch_assoc()) {
    $tgl = $row['tanggal_terbit'] ? date("d/m/Y", strtotime($row['tanggal_terbit'])) : '-';
    echo "<tr>
            <td>{$no}</td>
            <td>{$tgl}</td>
            <td>".htmlspecialchars($row['nama_media'])."</td>
            <td>".htmlspecialchars($row['judul_iklan'])."</td>
            <td>".htmlspecialchars($row['kategori_iklan'])."</td>
          </tr>";
    $no++;
}
echo "</table>";
