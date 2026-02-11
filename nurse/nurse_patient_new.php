<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'nurse') {
    header("Location: ../auth/index.php");
    exit;
}

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $full_name = trim($_POST['full_name']);
    $dni = trim($_POST['dni']);
    $birth_date = trim($_POST['birth_date']);
    $status = "Activo";

    if ($full_name === "" || $dni === "" || $birth_date === "") {
        $error = "Todos los campos son obligatorios.";
    } else {
        $stmt = $conn->prepare("
            INSERT INTO patients (full_name, dni, birth_date, status, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->bind_param("ssss", $full_name, $dni, $birth_date, $status);
        
        if ($stmt->execute()) {
            $success = "Paciente registrado correctamente.";
        } else {
            $error = "Error al registrar paciente.";
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nuevo Paciente</title>
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

        <h1>Registrar Nuevo Paciente</h1>

        <?php if ($error): ?>
            <div class="alert error"><?= $error ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert success"><?= $success ?></div>
        <?php endif; ?>

        <form method="POST" class="form-card">

            <div class="form-group">
                <label>Nombre completo *</label>
                <input type="text" name="full_name" required>
            </div>

            <div class="form-group">
                <label>DNI *</label>
                <input type="text" name="dni" required>
            </div>

            <div class="form-group">
                <label>Fecha de nacimiento *</label>
                <input type="date" name="birth_date" required>
            </div>

            <button class="btn-primary">Guardar Paciente</button>

        </form>

    </main>

</div>

</body>
</html>
