<?php
require '../../../vendor/autoload.php';
include "../../../inc/koneksi.php";

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Ambil filter dari URL
$date_from = $_GET['date_from'] ?? '';
$date_to   = $_GET['date_to']   ?? '';
$sentimen  = $_GET['sentimen']  ?? '';
$prioritas = $_GET['prioritas'] ?? '';

$where = [];
if ($date_from) $where[] = "tgl_input >= '$date_from'";
if ($date_to)   $where[] = "tgl_input <= '$date_to'";
if ($sentimen)  $where[] = "sentimen = '$sentimen'";
if ($prioritas) $where[] = "kategori_program = '$prioritas'";

$whereSql = $where ? "WHERE ".implode(" AND ",$where) : "";

// Query Data
$query = mysqli_query($koneksi, "
  SELECT tgl_input, nama_media, judul_berita, sentimen, kategori_program
  FROM kliping_berita
  $whereSql
  ORDER BY tgl_input DESC
");

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Judul Laporan
$sheet->mergeCells('A1:F1');
$sheet->setCellValue('A1', 'LAPORAN DATA KLIPING BERITA - FILTER');
$sheet->getStyle('A1')->getFont()->setSize(14)->setBold(true);
$sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

// Info Filter
$filterInfo = "Filter : ";
$filterInfo .= $date_from ? "Dari $date_from " : "";
$filterInfo .= $date_to ? "s/d $date_to " : "";
$filterInfo .= $sentimen ? "| Sentimen: $sentimen " : "";
$filterInfo .= $prioritas ? "| Kategori: $prioritas" : "";

$sheet->mergeCells('A2:F2');
$sheet->setCellValue('A2', $filterInfo);
$sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11);

// Header tabel
$sheet->setCellValue('A4', 'No');
$sheet->setCellValue('B4', 'Tanggal');
$sheet->setCellValue('C4', 'Media');
$sheet->setCellValue('D4', 'Judul Berita');
$sheet->setCellValue('E4', 'Sentimen');
$sheet->setCellValue('F4', 'Kategori');

$sheet->getStyle('A4:F4')->getFont()->setBold(true);
$sheet->getStyle('A4:F4')->getAlignment()->setHorizontal('center');
$sheet->getStyle('A4:F4')->getBorders()->getAllBorders()
      ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK);

// Isi Data
$rowNum = 5;
$no = 1;

while ($r = mysqli_fetch_assoc($query)) {
    $sheet->setCellValue("A$rowNum", $no++);
    $sheet->setCellValue("B$rowNum", date("d/m/Y", strtotime($r['tgl_input'])));
    $sheet->setCellValue("C$rowNum", $r['nama_media']);
    $sheet->setCellValue("D$rowNum", $r['judul_berita']);
    $sheet->setCellValue("E$rowNum", $r['sentimen']);
    $sheet->setCellValue("F$rowNum", $r['kategori_program']);

    $sheet->getStyle("A$rowNum:F$rowNum")->getBorders()->getAllBorders()
          ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

    $rowNum++;
}

// Auto width
foreach (range('A', 'F') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Download Excel
$filename = "kliping_filtered.xlsx";
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header("Content-Disposition: attachment;filename=\"$filename\"");
header("Cache-Control: max-age=0");

$writer = new Xlsx($spreadsheet);
$writer->save("php://output");
exit;

?>
