<?php
$host = $_ENV["DB_HOST"];
$port = $_ENV["DB_PORT"];
$dbname = $_ENV["DB_NAME"];
$user = $_ENV["DB_USER"];
$pass = $_ENV["DB_PASS"];
$charset = "utf8mb4";

$dns = "mysql:host=$host;port=$port;dbname=$dbname;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];
try {
    $pdo = new PDO($dns, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

?>