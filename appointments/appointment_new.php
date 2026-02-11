<?php
session_start();
require_once __DIR__ . '/../includes/db.php'; 

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'doctor') {
    header("Location: ../auth/index.php");
    exit;
}

$doctor_id = $_SESSION['user_id'];
$error = "";

// paciente preseleccionado (si viene desde perfil)
$selected_patient_id = intval($_GET['patient_id'] ?? 0);

// Obtener pacientes
$patients = $conn->query("SELECT id, full_name FROM patients ORDER BY full_name ASC");

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patient_id = intval($_POST['patient_id']);
    $date       = $_POST['date'] ?? '';
    $time       = $_POST['time'] ?? '';
    $reason     = trim($_POST['reason'] ?? '');

    if ($patient_id === 0 || $date === "" || $time === "") {
        $error = "Paciente, fecha y hora son obligatorios.";
    } else {
        $stmt = $conn->prepare("
            INSERT INTO appointments (patient_id, doctor_id, date, time, reason)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("iisss", $patient_id, $doctor_id, $date, $time, $reason);

        if ($stmt->execute()) {
            // Volver SIEMPRE al perfil del paciente
            header("Location: ../doctor/patient_view.php?id=" . $patient_id);
            exit;
        } else {
            $error = "Error al guardar la cita.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nueva Cita Médica - MedConnect</title>
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/auth.css">
</head>
<body>

<div class="layout">

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <h2 class="logo">MedConnect</h2>
        <nav>
            <a href="../doctor/doctor_dashboard.php">Dashboard</a>
            <a href="../appointments/appointments.php">Citas</a>
            <a href="../doctor/patients.php">Pacientes</a>
            <a href="../auth/logout.php" class="logout">Cerrar Sesión</a>
        </nav>
    </aside>

    <!-- CONTENIDO -->
    <main class="content">

        <!-- TOP ROW -->
        <div class="top-row">
            <h1>Nueva Cita Médica</h1>

            <?php if ($selected_patient_id > 0): ?>
                <a href="../doctor/patient_view.php?id=<?= $selected_patient_id ?>" class="btn-primary">
                    ← Volver al paciente
                </a>
            <?php else: ?>
                <a href="../appointments/appointments.php" class="btn-primary">
                    ← Volver a citas
                </a>
            <?php endif; ?>
        </div>

        <?php if ($error): ?>
            <div class="alert error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" class="form-card">

            <div class="form-group">
                <label>Paciente *</label>
                <select name="patient_id" required>
                    <option value="">-- Selecciona un paciente --</option>
                    <?php while ($p = $patients->fetch_assoc()): ?>
                        <option value="<?= $p['id'] ?>"
                            <?= $selected_patient_id === (int)$p['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['full_name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Fecha *</label>
                    <input type="date" name="date" required>
                </div>

                <div class="form-group">
                    <label>Hora *</label>
                    <input type="time" name="time" required>
                </div>
            </div>

            <div class="form-group">
                <label>Motivo</label>
                <input type="text" name="reason" placeholder="Motivo de la cita">
            </div>

            <button class="btn-primary">Guardar Cita</button>
        </form>

    </main>
</div>

</body>
</html>
