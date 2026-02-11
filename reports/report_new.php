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
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if ($title === "" || $content === "") {
        $error = "Todos los campos son obligatorios.";
    } else {
        $stmt = $conn->prepare("
            INSERT INTO reports (patient_id, doctor_id, title, content, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->bind_param("iiss", $patient_id, $_SESSION['user_id'], $title, $content);

        if ($stmt->execute()) {
            header("Location: ../doctor/patient_view.php?id=" . $patient_id);
            exit;
        } else {
            $error = "Error al guardar el informe.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nuevo Informe Médico - MedConnect</title>
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/auth.css">
</head>
<body>

<div class="layout">

    <aside class="sidebar">
        <h2 class="logo">MedConnect</h2>
        <nav>
            <a href="../doctor/doctor_dashboard.php">Dashboard</a>
            <a href="../doctor/patients.php">Pacientes</a>
            <a href="../auth/logout.php" class="logout">Cerrar Sesión</a>
        </nav>
    </aside>

    <main class="content">

        <!-- TOP ROW -->
        <div class="top-row">
            <h1>Nuevo Informe Médico</h1>
            <a href="../doctor/patient_view.php?id=<?= $patient_id ?>" class="btn-primary">
                ← Volver al paciente
            </a>
        </div>

        <?php if ($error): ?>
            <div class="alert error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" class="form-card">

            <div class="form-group">
                <label>Título del informe *</label>
                <input type="text" name="title" required>
            </div>

            <div class="form-group">
                <label>Contenido del informe *</label>
                <textarea name="content" rows="8" required></textarea>
            </div>

            <button class="btn-primary">Guardar Informe</button>
        </form>

    </main>
</div>

</body>
</html>
