<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'nurse') {
    header("Location: ../auth/index.php");
    exit;
}

$nurse_id = $_SESSION['user_id'];

// Registros del día
$today = date("Y-m-d");
$stmt = $conn->prepare("
    SELECT COUNT(*) AS total 
    FROM nurse_records 
    WHERE DATE(record_date) = ? AND nurse_id = ?
");
$stmt->bind_param("si", $today, $nurse_id);
$stmt->execute();
$today_total = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// Pacientes atendidos hoy (distintos)
$stmt = $conn->prepare("
    SELECT COUNT(DISTINCT patient_id) AS total
    FROM nurse_records
    WHERE DATE(record_date) = ? AND nurse_id = ?
");
$stmt->bind_param("si", $today, $nurse_id);
$stmt->execute();
$patients_today = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// Total registros de enfermería de esta enfermera
$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM nurse_records
    WHERE nurse_id = ?
");
$stmt->bind_param("i", $nurse_id);
$stmt->execute();
$total_records = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// Últimos registros
$stmt = $conn->prepare("
    SELECT nr.*, p.full_name
    FROM nurse_records nr
    JOIN patients p ON p.id = nr.patient_id
    WHERE nr.nurse_id = ?
    ORDER BY nr.record_date DESC
");
$stmt->bind_param("i", $nurse_id);
$stmt->execute();
$records = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Enfermería - MedConnect</title>
    <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body>

<div class="layout">

    <aside class="sidebar">
        <h2 class="logo">MedConnect</h2>
        <nav>
            <a href="../nurse/nurse_dashboard.php" class="active">Panel Enfermería</a>
            <a href="../nurse/nurse_record_new.php">Nuevo Registro</a>
            <a href="../nurse/nurse_patients_attended.php">Pacientes Atendidos</a>
            <a href="../auth/logout.php" class="logout">Cerrar Sesión</a>
        </nav>
    </aside>

    <main class="content">

        <h1>Bienvenida/o, <?= htmlspecialchars($_SESSION['user_name']) ?></h1>

        <!-- CARDS -->
        <div class="cards">
            <div class="card">
                <h3>Registros de hoy</h3>
                <p><?= $today_total ?></p>
            </div>

            <div class="card">
                <h3>Pacientes atendidos</h3>
                <p><?= $patients_today ?></p>
            </div>

            <div class="card">
                <h3>Registros totales</h3>
                <p><?= $total_records ?></p>
            </div>
        </div>

        <div class="top-row">
            <h2>Últimos registros de enfermería</h2>
            <!-- AHORA VA AL FORMULARIO DE ENFERMERÍA -->
            <a href="../nurse/nurse_record_new.php" class="btn-primary">+ Nuevo Paciente</a>
        </div>

        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Paciente</th>
                        <th>Tipo</th>
                        <th>Descripción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($r = $records->fetch_assoc()): ?>
                        <tr>
                            <td><?= $r['record_date'] ?></td>
                            <td><?= htmlspecialchars($r['full_name']) ?></td>
                            <td><?= htmlspecialchars($r['type']) ?></td>
                            <td><?= htmlspecialchars($r['description']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

    </main>

</div>

</body>
</html>
