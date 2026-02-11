<?php
session_start();
require_once __DIR__ . '/../includes/db.php'; 
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'doctor') {
    header("Location: ../auth/index.php");
    exit;
}

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name  = trim($_POST['full_name'] ?? '');
    $dni        = trim($_POST['dni'] ?? '');
    $birth_date = $_POST['birth_date'] ?? '';
    $gender     = $_POST['gender'] ?? 'other';
    $phone      = trim($_POST['phone'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $address    = trim($_POST['address'] ?? '');
    $status     = $_POST['status'] ?? 'Activo';

    if ($full_name === "" || $dni === "" || $birth_date === "") {
        $error = "Nombre, DNI y fecha de nacimiento son obligatorios.";
    } else {
        $stmt = $conn->prepare("INSERT INTO patients (full_name, dni, birth_date, gender, phone, email, address, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $full_name, $dni, $birth_date, $gender, $phone, $email, $address, $status);

        if ($stmt->execute()) {
            $success = "Paciente registrado correctamente.";
        } else {
            $error = "Error al guardar el paciente (¿DNI duplicado?).";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nuevo Paciente - MedConnect</title>
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/auth.css">

</head>
<body>

<div class="layout">

    <aside class="sidebar">
        <h2 class="logo">MedConnect</h2>

        <nav>
            <a href="../doctor/doctor_dashboard.php">Dashboard</a>
            <a href="../doctor/patients.php">Lista de Pacientes</a>
            <a href="../auth/logout.php" class="logout">Cerrar Sesión</a>
        </nav>
    </aside>

    <main class="content">
        <h1>Nuevo Paciente</h1>

        <?php if ($error): ?>
            <div class="alert error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST" action="" class="form-card">
            <div class="form-row">
                <div class="form-group">
                    <label>Nombre Completo *</label>
                    <input type="text" name="full_name" required>
                </div>
                <div class="form-group">
                    <label>DNI *</label>
                    <input type="text" name="dni" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Fecha de nacimiento *</label>
                    <input type="date" name="birth_date" required>
                </div>
                <div class="form-group">
                    <label>Género</label>
                    <select name="gender">
                        <option value="male">Hombre</option>
                        <option value="female">Mujer</option>
                        <option value="other">Otro</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Teléfono</label>
                    <input type="text" name="phone">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email">
                </div>
            </div>

            <div class="form-group">
                <label>Dirección</label>
                <input type="text" name="address">
            </div>

            <div class="form-group">
                <label>Estado</label>
                <select name="status">
                    <option value="Activo">Activo</option>
                    <option value="Inactivo">Inactivo</option>
                </select>
            </div>

            <button type="submit" class="btn-primary">Guardar Paciente</button>
        </form>
    </main>
</div>

</body>
</html>
