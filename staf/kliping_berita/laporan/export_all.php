<?php
require '../../../vendor/autoload.php';
include "../../../inc/koneksi.php";

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Judul Laporan
$sheet->mergeCells('A1:F1');
$sheet->setCellValue('A1', 'LAPORAN DATA KLIPING BERITA');
$sheet->getStyle('A1')->getFont()->setSize(14)->setBold(true);

// Header tabel
$sheet->setCellValue('A3', 'No');
$sheet->setCellValue('B3', 'Tanggal');
$sheet->setCellValue('C3', 'Media');
$sheet->setCellValue('D3', 'Judul Berita');
$sheet->setCellValue('E3', 'Sentimen');
$sheet->setCellValue('F3', 'Kategori');

$sheet->getStyle('A3:F3')->getFont()->setBold(true);
$sheet->getStyle('A3:F3')->getBorders()->getAllBorders()
      ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK);

// Ambil data dari database
$query = mysqli_query($koneksi, "
  SELECT tgl_input, nama_media, judul_berita, sentimen, kategori_program
  FROM kliping_berita
  ORDER BY tgl_input DESC
");

$rowNum = 4;
$no = 1;

while ($r = mysqli_fetch_assoc($query)) {
    $sheet->setCellValue("A$rowNum", $no++);
    $sheet->setCellValue("B$rowNum", date("d/m/Y", strtotime($r['tgl_input'])));
    $sheet->setCellValue("C$rowNum", $r['nama_media']);
    $sheet->setCellValue("D$rowNum", $r['judul_berita']);
    $sheet->setCellValue("E$rowNum", $r['sentimen']);
    $sheet->setCellValue("F$rowNum", $r['kategori_program']);

    // border dari A sampai F
    $sheet->getStyle("A$rowNum:F$rowNum")->getBorders()->getAllBorders()
          ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

    $rowNum++;
}

// Auto width
foreach (range('A','F') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Download Excel
$filename = "data-kliping-berita.xlsx";
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header("Content-Disposition: attachment;filename=\"$filename\"");
header("Cache-Control: max-age=0");

$writer = new Xlsx($spreadsheet);
$writer->save("php://output");
exit;

?>
