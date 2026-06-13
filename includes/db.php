<?php
// Conexión a la base de datos

$envPhpPath = __DIR__ . '/../.env.php';
$envPath = __DIR__ . '/../.env';

if (file_exists($envPhpPath)) {
    require_once $envPhpPath;
} elseif (file_exists($envPath)) {
    require_once $envPath;
}

$host = defined('DB_HOST') ? DB_HOST : 'localhost';
$user = defined('DB_USER') ? DB_USER : 'root';
$pass = defined('DB_PASS') ? DB_PASS : 'root';
$dbname = defined('DB_NAME') ? DB_NAME : 'medicodb';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$conn->set_charset("utf8");

?>
