<?php
require_once __DIR__ . "/../vendor/autoload.php";

use Dotenv\Dotenv;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$dotenv = Dotenv::createImmutable(__DIR__ . "/..");
$dotenv->safeLoad();

require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/occupation_functions.php";

try {
    $occupations = loadSummaryRows($pdo);
} catch (PDOException $e) {
    die("Database query failed: " . $e->getMessage());
}

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle("Ellies Map");

// header row
$headers = ["Post Number", "Location", "Occupation Type", "Character Name", "Weapons", "Zombie Types", "Observation"];
$columnIndex = "A";
foreach ($headers as $header) {
    $sheet->setCellValue($columnIndex . "1", $header);
    $columnIndex++;
}

// data rows
$rowNum = 2;
foreach ($occupations as $row) {
    $sheet->setCellValue("A" . $rowNum, $row["post_number"]);
    $sheet->setCellValue("B" . $rowNum, $row["location"]);
    $sheet->setCellValue("C" . $rowNum, $row["occupation_type"]);
    $sheet->setCellValue("D" . $rowNum, $row["name"] ?? "NULL");
    $sheet->setCellValue("E" . $rowNum, $row["weapons"] ?? "NULL");
    $sheet->setCellValue("F" . $rowNum, $row["zombie_types"] ?? "NULL");
    $sheet->setCellValue("G" . $rowNum, $row["observation"] ?? "NULL");
    $rowNum++;
}

$filename = "ellies_map_" . date("Ymd_His") . ".xlsx";

header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header("Content-Disposition: attachment; filename=\"" . $filename . "\"");
header("Cache-Control: max-age=0");

$writer = new Xlsx($spreadsheet);
$writer->save("php://output");
exit();
?>