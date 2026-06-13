<?php
// Configuración de la conexión a la base de datos
$host = "localhost";
$user = "u205971234_medico_user";
$pass = "dOZV!hB|S7";
$dbname = "u205971234_medico_db";

$conn = new mysqli($host, $user, $pass, $dbname);

// Verificar si hay error de conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Establecer el charset de la conexión
$conn->set_charset("utf8");

?>