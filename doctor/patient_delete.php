<?php
session_start();
require_once __DIR__ . '/../includes/db.php'; 
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'doctor') {
    header("Location: ../auth/index.php");
    exit;
}

$patient_id = intval($_GET['id'] ?? 0);
if ($patient_id <= 0) {
    header("Location: ../doctor/patients.php");
    exit;
}

// Gracias a ON DELETE CASCADE en las foreign keys, se borran también
// consultas, informes y registros de enfermería relacionados.
$stmt = $conn->prepare("DELETE FROM patients WHERE id = ?");
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$stmt->close();

header("Location: ../doctor/patients.php");
exit;
