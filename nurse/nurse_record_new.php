<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'nurse') {
    header("Location: ../auth/index.php");
    exit;
}

$nurse_id = $_SESSION['user_id'];

$error = "";
$success = "";

// Obtener lista de pacientes para el select
$patients_result = $conn->query("SELECT id, full_name FROM patients ORDER BY full_name ASC");

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patient_id  = intval($_POST['patient_id'] ?? 0);
    $type        = trim($_POST['type'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($patient_id <= 0 || $type === "" || $description === "") {
        $error = "Todos los campos son obligatorios.";
    } else {
        $stmt = $conn->prepare("
            INSERT INTO nurse_records (patient_id, nurse_id, record_date, type, description) 
            VALUES (?, ?, NOW(), ?, ?)
        ");
        $stmt->bind_param("iiss", $patient_id, $nurse_id, $type, $description);

        if ($stmt->execute()) {
            $success = "Registro de enfermería añadido correctamente.";
        } else {
            $error = "Error al guardar el registro.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nuevo Registro de Enfermería</title>
    <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body>

<div class="layout">

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <h2 class="logo">MedConnect</h2>
        <nav>
            <a href="../nurse/nurse_dashboard.php">Panel Enfermería</a>
            <a href="../nurse/nurse_record_new.php" class="active">Nuevo Registro</a>
            <a href="../nurse/nurse_patients_attended.php">Pacientes Atendidos</a>
            <a href="../auth/logout.php" class="logout">Cerrar Sesión</a>
        </nav>
    </aside>

    <!-- CONTENIDO -->
    <main class="content">

        <h1>Nuevo Registro de Enfermería</h1>

        <?php if ($error): ?>
            <div class="alert error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST" class="form-card">

           <div class="form-group">
    <label>Paciente *</label>
    <select name="patient_id" required>
        <option value="">-- Selecciona un paciente --</option>
        <?php while ($p = $patients_result->fetch_assoc()): ?>
            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['full_name']) ?></option>
        <?php endwhile; ?>
    </select>

    <!-- Botón para crear un nuevo paciente -->
    <a href="nurse_patient_new.php" class="btn-primary" 
       style="margin-top:10px; display:inline-block;">+ Crear nuevo paciente</a>
</div>


            <div class="form-group">
                <label>Tipo de registro *</label>
                <input type="text" name="type" placeholder="Cura, tratamiento, observación..." required>
            </div>

            <div class="form-group">
                <label>Descripción *</label>
                <textarea name="description" rows="6"
                          style="width:100%; padding:10px; border-radius:10px; border:1px solid #d1d5db;"
                          required></textarea>
            </div>

            <button type="submit" class="btn-primary">Guardar Registro</button>
        </form>

    </main>
</div>

</body>
</html>
