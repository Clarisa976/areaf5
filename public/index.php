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
    $stmt = $pdo->query("SELECT COUNT(*) AS total_posts FROM posts");
    $result = $stmt->fetch();
    $totalPosts = $result['total_posts'];
} catch (\PDOException $e) {
    die("Database query failed: " . $e->getMessage());
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AreaF5 - Test Técnico</title>
</head>
<body>
    <h1>Área F5 - Test Técnico</h1>
    <p>Conexión :D</p>
    <p>Total de puestos: <?php echo $totalPosts; ?></p>
</body>
</html>