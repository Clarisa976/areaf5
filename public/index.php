<?php
require_once __DIR__ . "/../vendor/autoload.php";

use Dotenv\Dotenv;

// initialize Dotenv to load environment variables from .env file
$dotenv = Dotenv::createImmutable(__DIR__ . "/..");
$dotenv->load();

// include the database configuration file
require_once __DIR__ . "/../config/db.php";

// test the database connection
try {
    $sql = "SELECT post_number, location, occupation_type, name, weapons, zombie_types, observation FROM v_post_summary ORDER BY post_number, occupation_type";
    $stmt = $pdo->query($sql);
    $result = $stmt->fetchAll();
} catch (\PDOException $e) {
    die("Database query failed: " . $e->getMessage());
}
require_once __DIR__ . '/../views/list.php';
?>
