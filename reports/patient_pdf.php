<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../fpdf/fpdf.php';

$patient_id = intval($_GET['id'] ?? 0);

if ($patient_id <= 0) {
    die("Paciente inválido");
}

// Obtenemos datos del paciente
$stmt = $conn->prepare("SELECT * FROM patients WHERE id = ?");
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$patient = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$patient) {
    die("Paciente no encontrado");
}

// Crear PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);

$pdf->Cell(0,10,'Informe del Paciente',0,1,'C');
$pdf->Ln(5);

$pdf->SetFont('Arial','',12);
$pdf->Cell(0,10,"Nombre: " . $patient['full_name'], 0, 1);
$pdf->Cell(0,10,"DNI: " . $patient['dni'], 0, 1);
$pdf->Cell(0,10,"Estado: " . $patient['status'], 0, 1);
$pdf->Cell(0,10,"Telefono: " . $patient['phone'], 0, 1);
$pdf->Cell(0,10,"Email: " . $patient['email'], 0, 1);

$pdf->Output();
exit;
