<?php
session_start();
require_once __DIR__ . '/../includes/db.php'; 

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'doctor') {
    header("Location: ../auth/index.php");
    exit;
}

$patient_id = intval($_GET['patient_id'] ?? 0);
if ($patient_id <= 0) {
    header("Location: ../doctor/patients.php");
    exit;
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reason      = trim($_POST['reason'] ?? '');
    $diagnosis   = trim($_POST['diagnosis'] ?? '');
    $treatment   = trim($_POST['treatment'] ?? '');
    $notes       = trim($_POST['notes'] ?? '');

    if ($reason === "" || $diagnosis === "" || $treatment === "" || $notes === "") {
        $error = "Todos los campos son obligatorios.";
    } else {
        $stmt = $conn->prepare("
            INSERT INTO consultations 
            (patient_id, doctor_id, visit_date, reason, diagnosis, treatment, notes)
            VALUES (?, ?, NOW(), ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            "iissss",
            $patient_id,
            $_SESSION['user_id'],
            $reason,
            $diagnosis,
            $treatment,
            $notes
        );

        if ($stmt->execute()) {
            header("Location: ../doctor/patient_view.php?id=" . $patient_id);
            exit;
        } else {
            $error = "Error al guardar la consulta.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nueva Consulta - MedConnect</title>
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
            <a href="../doctor/patients.php">Pacientes</a>
            <a href="../auth/logout.php" class="logout">Cerrar Sesión</a>
        </nav>
    </aside>

    <!-- CONTENIDO -->
    <main class="content">

        <div class="top-row">
            <h1>Nueva Consulta</h1>
            <a href="../doctor/patient_view.php?id=<?= $patient_id ?>" class="btn-primary">
                ← Volver al paciente
            </a>
        </div>

        <?php if ($error): ?>
            <div class="alert error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" class="form-card">

            <div class="form-group">
                <label>Motivo de la consulta *</label>
                <input type="text" name="reason" required>
            </div>

            <div class="form-group">
                <label>Diagnóstico *</label>
                <input type="text" name="diagnosis" required>
            </div>

            <div class="form-group">
                <label>Tratamiento *</label>
                <textarea name="treatment" rows="4" required></textarea>
            </div>

            <div class="form-group">
                <label>Notas médicas *</label>
                <textarea name="notes" rows="5" required></textarea>
            </div>

            <button class="btn-primary">Guardar Consulta</button>
        </form>

    </main>
</div>

</body>
</html>
