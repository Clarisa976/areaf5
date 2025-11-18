<?php
require_once __DIR__ . "/../vendor/autoload.php";

use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__ . "/..");
$dotenv->safeLoad();

require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/occupation_functions.php";

if(!isset($_POST["delete"], $_POST["occupation_id"])){
    header("Location: index.php");
    exit();
}

$occupationId = (int)$_POST["occupation_id"];
if ($occupationId <= 0) {
    header("Location: index.php");
    exit();
}

deleteOccupation($pdo, $occupationId);
header("Location: index.php");
exit();
?>