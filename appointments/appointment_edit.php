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

// Obtener datos de la cita
$stmt = $conn->prepare("
    SELECT * FROM appointments WHERE id = ?
");
$stmt->bind_param("i", $appointment_id);
$stmt->execute();
$appointment = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$appointment) {
    die("Cita no encontrada");
}

// Obtener lista de pacientes
$patients = $conn->query("SELECT id, full_name FROM patients ORDER BY full_name ASC");

// Guardar cambios
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $patient_id = intval($_POST['patient_id']);
    $date = $_POST['date'];
    $time = $_POST['time'];
    $reason = trim($_POST['reason']);

    $update = $conn->prepare("
        UPDATE appointments 
        SET patient_id = ?, date = ?, time = ?, reason = ?
        WHERE id = ?
    ");
    $update->bind_param("isssi", $patient_id, $date, $time, $reason, $appointment_id);
    $update->execute();
    $update->close();

    header("Location: appointments.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Cita - MedConnect</title>
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
        <h1>Editar Cita</h1>

        <div class="form-card">

            <form method="POST">

                <div class="form-group">
                    <label>Paciente</label>
                    <select name="patient_id" required>
                        <?php while ($p = $patients->fetch_assoc()): ?>
                            <option value="<?= $p['id'] ?>" 
                                <?= $p['id'] == $appointment['patient_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p['full_name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Fecha</label>
                    <input type="date" name="date" value="<?= $appointment['date'] ?>" required>
                </div>

                <div class="form-group">
                    <label>Hora</label>
                    <input type="time" name="time" value="<?= $appointment['time'] ?>" required>
                </div>

                <div class="form-group">
                    <label>Motivo</label>
                    <input type="text" name="reason" value="<?= htmlspecialchars($appointment['reason']) ?>" required>
                </div>

                <button class="btn-primary">Guardar Cambios</button>

            </form>

        </div>

    </main>

</div>

</body>
</html>
