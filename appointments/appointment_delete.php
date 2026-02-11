<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'doctor') {
    header("Location: ../auth/index.php");
    exit;
}

$appointment_id = intval($_GET['id'] ?? 0);

if ($appointment_id <= 0) {
    die("Cita inválida");
}

// Eliminar cita
$stmt = $conn->prepare("DELETE FROM appointments WHERE id = ?");
$stmt->bind_param("i", $appointment_id);
$stmt->execute();
$stmt->close();

header("Location: appointments.php");
exit;
