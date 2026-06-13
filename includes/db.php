<?php
// Conexión a la base de datos
$host = "localhost";
$user = "u205971234_medico_user";
$pass = "";
$dbname = "u205971234_medico_db";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$conn->set_charset("utf8");

?>