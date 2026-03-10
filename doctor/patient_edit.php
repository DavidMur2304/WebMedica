<?php
session_start();
require_once __DIR__ . '/../includes/db.php'; 
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'doctor') {
    header("Location: ../auth/index.php");
    exit;
}

$patient_id = intval($_GET['id'] ?? 0);
if ($patient_id <= 0) {
    header("Location: ../doctor/patients.php");
    exit;
}

$error = "";
$success = "";

// Obtener datos del paciente
$stmt = $conn->prepare("SELECT * FROM patients WHERE id = ?");
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$patient = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$patient) {
    header("Location: ../doctor/patients.php");
    exit;
}

// Procesar formulario
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
    } elseif ($phone !== "" && !preg_match('/^[0-9]+$/', $phone)) {
        $error = "El teléfono solo puede contener números.";
    } else {
        $stmt = $conn->prepare("
            UPDATE patients 
            SET full_name = ?, dni = ?, birth_date = ?, gender = ?, phone = ?, email = ?, address = ?, status = ?
            WHERE id = ?
        ");
        $stmt->bind_param("ssssssssi", $full_name, $dni, $birth_date, $gender, $phone, $email, $address, $status, $patient_id);

        if ($stmt->execute()) {
            $success = "Paciente actualizado correctamente.";
            // volver a cargar datos para ver cambios
            $patient['full_name']  = $full_name;
            $patient['dni']        = $dni;
            $patient['birth_date'] = $birth_date;
            $patient['gender']     = $gender;
            $patient['phone']      = $phone;
            $patient['email']      = $email;
            $patient['address']    = $address;
            $patient['status']     = $status;
        } else {
            $error = "Error al actualizar el paciente.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Paciente - MedConnect</title>
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/auth.css">

</head>
<body>

<div class="layout">
    <aside class="sidebar">
        <h2 class="logo">MedConnect</h2>
        <nav>
            <a href="../doctor/doctor_dashboard.php">Dashboard</a>
            <a href="../doctor/patients.php" class="active">Pacientes</a>
            <a href="../auth/logout.php" class="logout">Cerrar Sesión</a>
        </nav>
    </aside>

    <main class="content">
        <div class="top-row">
            <h1>Editar Paciente</h1>
            <a href="patients.php" class="btn-secondary">← Volver a pacientes</a>
        </div>

        <?php if ($error): ?>
            <div class="alert error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST" class="form-card">
            <div class="form-row">
                <div class="form-group">
                    <label>Nombre Completo *</label>
                    <input type="text" name="full_name" value="<?= htmlspecialchars($patient['full_name']) ?>" required>
                </div>
                <div class="form-group">
                    <label>DNI *</label>
                    <input type="text" name="dni" value="<?= htmlspecialchars($patient['dni']) ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Fecha de nacimiento *</label>
                    <input type="date" name="birth_date" value="<?= htmlspecialchars($patient['birth_date']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Género</label>
                    <select name="gender">
                        <option value="male"   <?= $patient['gender'] === 'male' ? 'selected' : ''  ?>>Hombre</option>
                        <option value="female" <?= $patient['gender'] === 'female' ? 'selected' : ''?>>Mujer</option>
                        <option value="other"  <?= $patient['gender'] === 'other' ? 'selected' : '' ?>>Otro</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Teléfono</label>
                    <input type="tel" name="phone" value="<?= htmlspecialchars($patient['phone']) ?>" inputmode="numeric" pattern="[0-9]*" title="Solo números" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($patient['email']) ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Dirección</label>
                <input type="text" name="address" value="<?= htmlspecialchars($patient['address']) ?>">
            </div>

            <div class="form-group">
                <label>Estado</label>
                <select name="status">
                    <option value="Activo"   <?= $patient['status'] === 'Activo' ? 'selected' : ''   ?>>Activo</option>
                    <option value="Inactivo" <?= $patient['status'] === 'Inactivo' ? 'selected' : '' ?>>Inactivo</option>
                </select>
            </div>

            <button class="btn-primary">Guardar Cambios</button>
        </form>
    </main>
</div>

</body>
</html>
