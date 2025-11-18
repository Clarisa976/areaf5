<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__ . "/..");
$dotenv->safeLoad();

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/occupation_functions.php';

try {
    $occupations = loadSummaryRows($pdo);
} catch (PDOException $e) {
    die("Database query failed: " . $e->getMessage());
}

$filename = "ellies_map_" . date("Ymd_His") . ".csv";

header("Content-Type: text/csv; charset=utf-8");
header("Content-Disposition: attachment; filename=\"$filename\"");

$output = fopen('php://output', 'w');
$header = ["Post Number", "Location", "Occupation Type", "Character Name", "Weapons", "Zombie Types", "Observation"];
fputcsv($output, $header);

try {
    foreach ($occupations as $occupation) {
        $row = [
            $occupation['post_number'],
            $occupation['location'],
            $occupation['occupation_type'],
            $occupation['name'] ?? 'NULL',
            $occupation['weapons'] ?? 'NULL',
            $occupation['zombie_types'] ?? 'NULL',
            $occupation['observation'] ?? 'NULL'
        ];
        fputcsv($output, $row);
    }
} catch (PDOException $e) {
    die("Database query failed: " . $e->getMessage());
}

fclose($output);
?>