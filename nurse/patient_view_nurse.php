<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'nurse') {
    header("Location: ../auth/index.php");
    exit;
}

$patient_id = intval($_GET['id'] ?? 0);

if ($patient_id <= 0) {
    die("Paciente inválido");
}

$stmt = $conn->prepare("SELECT * FROM patients WHERE id = ?");
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$patient = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Registros enfermería
$stmt = $conn->prepare("
    SELECT nr.*, u.name AS nurse_name
    FROM nurse_records nr
    JOIN users u ON u.id = nr.nurse_id
    WHERE nr.patient_id = ?
    ORDER BY record_date DESC
");
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$records = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial Enfermería - MedConnect</title>
    <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body>

<div class="layout">

    <aside class="sidebar">
        <h2 class="logo">MedConnect</h2>
        <nav>
            <a href="../nurse/nurse_dashboard.php">Panel Enfermería</a>
            <a href="../nurse/nurse_record_new.php">Nuevo Registro</a>
            <a href="../nurse/nurse_patients_attended.php">Pacientes Atendidos</a>
            <a href="../auth/logout.php" class="logout">Cerrar Sesión</a>
        </nav>
    </aside>

    <main class="content">

        <h1>Historial de Enfermería</h1>

        <div class="patient-header">
            <div>
                <h2><?= htmlspecialchars($patient['full_name']) ?></h2>
                <p>DNI: <?= htmlspecialchars($patient['dni']) ?></p>
                <p>Estado: <?= htmlspecialchars($patient['status']) ?></p>
            </div>
        </div>

        <h2>Registros</h2>

        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Descripción</th>
                        <th>Registrado por</th>
                    </tr>
                </thead>
                <tbody>

                <?php while ($r = $records->fetch_assoc()): ?>
                    <tr>
                        <td><?= $r['record_date'] ?></td>
                        <td><?= htmlspecialchars($r['type']) ?></td>
                        <td><?= nl2br(htmlspecialchars($r['description'])) ?></td>
                        <td><?= htmlspecialchars($r['nurse_name']) ?></td>
                    </tr>
                <?php endwhile; ?>

                </tbody>
            </table>
        </div>

    </main>
</div>

</body>
</html>
