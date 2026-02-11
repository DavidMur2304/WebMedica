<?php
$servername = "localhost";
$username = "u205971234_medico_user"; 
$password = "medCenter200423"; 
$dbname = "u205971234_medico_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
?>
