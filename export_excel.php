<?php
// Include Composer's autoload file
require 'vendor/autoload.php';
include 'db_connect.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set header names
$sheet->setCellValue('A1', 'ID');
$sheet->setCellValue('B1', 'Student Code');
$sheet->setCellValue('C1', 'Student Name');
$sheet->setCellValue('D1', 'Class');
$sheet->setCellValue('E1', 'Subjects');
$sheet->setCellValue('F1', 'Average');

// Fetch data from the database
$where = "";
if (isset($_SESSION['rs_id'])) {
    $where = " WHERE r.student_id = {$_SESSION['rs_id']} ";
}
$qry = $conn->query("SELECT r.*, concat(s.firstname, ' ', s.middlename, ' ', s.lastname) as name, s.student_code, concat(c.level, '-', c.section) as class FROM results r INNER JOIN classes c ON c.id = r.class_id INNER JOIN students s ON s.id = r.student_id $where ORDER BY unix_timestamp(r.date_created) DESC");

$rowCount = 2;
while ($row = $qry->fetch_assoc()) {
    $subjects = $conn->query("SELECT * FROM result_items WHERE result_id =" . $row['id'])->num_rows;

    $sheet->setCellValue('A' . $rowCount, $rowCount - 1);
    $sheet->setCellValue('B' . $rowCount, $row['student_code']);
    $sheet->setCellValue('C' . $rowCount, $row['name']);
    $sheet->setCellValue('D' . $rowCount, $row['class']);
    $sheet->setCellValue('E' . $rowCount, $subjects);
    $sheet->setCellValue('F' . $rowCount, $row['marks_percentage']);

    $rowCount++;
}

// Write file
$writer = new Xlsx($spreadsheet);
$filename = 'student_results.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');
$writer->save('php://output');
exit;
?>
