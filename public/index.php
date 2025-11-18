<?php
require_once __DIR__ . "/../vendor/autoload.php";

use Dotenv\Dotenv;

// initialize Dotenv to load environment variables from .env file
$dotenv = Dotenv::createImmutable(__DIR__ . "/..");
$dotenv->load();

// include the database configuration file
require_once __DIR__ . "/../config/db.php";

// helper functions
require_once __DIR__ . "/../config/occupation_functions.php";

// load summary rows form the view
try {
    $rows = loadSummaryRows($pdo);
} catch (\PDOException $e) {
    die("Database query failed: " . $e->getMessage());
}
require_once __DIR__ . '/../views/list.php';
?>
