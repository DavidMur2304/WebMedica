<?php
session_start();
require_once __DIR__ . '/../includes/db.php'; 
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'doctor') {
    header("Location: ../auth/index.php");
    exit;
}

$doctor_id = $_SESSION['user_id'];

// Obtener citas
$stmt = $conn->prepare("
    SELECT a.*, p.full_name 
    FROM appointments a
    JOIN patients p ON p.id = a.patient_id
    WHERE a.doctor_id = ?
    ORDER BY a.date ASC, a.time ASC
");
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$appointments = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Citas Médicas - MedConnect</title>
    <link rel="stylesheet" href="../css/auth.css">
    <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body>
<div class="layout">

    <aside class="sidebar">
        <h2 class="logo">MedConnect</h2>
        <nav>
            <a href="../doctor/doctor_dashboard.php">Dashboard</a>
            <a href="../doctor/patients.php">Pacientes</a>
            <a href="../appointments/appointments.php" class="active">Citas</a>
            <a href="../auth/logout.php" class="logout">Cerrar Sesión</a>
        </nav>
    </aside>

    <main class="content">

        <div class="top-row">
            <h1>Citas Médicas</h1>
            <a href="../appointments/appointment_new.php" class="btn-primary">+ Nueva Cita</a>
        </div>

        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Hora</th>
                    <th>Paciente</th>
                    <th>Motivo</th>
                    <th>Acciones</th>
                </tr>
                </thead>

                <tbody>
                <?php if ($appointments->num_rows > 0): ?>
                    <?php while ($row = $appointments->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['date'] ?></td>
                            <td><?= $row['time'] ?></td>
                            <td><?= htmlspecialchars($row['full_name']) ?></td>
                            <td><?= htmlspecialchars($row['reason']) ?></td>
                            <td>
                                <a class="link" href="../appointments/appointment_edit.php?id=<?= $row['id'] ?>">Editar</a> |
                                <a class="link" href="../appointments/appointment_delete.php?id=<?= $row['id'] ?>"
                                   onclick="return confirm('¿Seguro de eliminar esta cita?');">Eliminar</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5">No hay citas programadas.</td></tr>
                <?php endif; ?>
                </tbody>

            </table>
        </div>

    </main>
</div>
</body>
</html>
