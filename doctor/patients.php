<?php
session_start();
require_once __DIR__ . '/../includes/db.php'; 

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'doctor') {
    header("Location: ../auth/index.php");
    exit;
}

$search = trim($_GET['search'] ?? '');

$sql = "SELECT * FROM patients";
if ($search !== '') {
    $sql .= " WHERE full_name LIKE ? OR dni LIKE ?";
    $like = "%$search%";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $like, $like);
} else {
    $stmt = $conn->prepare($sql);
}

$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista de Pacientes - MedConnect</title>

    <!-- SOLO dashboard.css -->
    <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body>

<div class="layout">

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <h2 class="logo">MedConnect</h2>

        <nav>
            <a href="../doctor/doctor_dashboard.php">Dashboard</a>
            <a href="../doctor/patients.php" class="active">Lista de Pacientes</a>
            <a href="../appointments/appointments.php">Citas</a>
            <a href="../auth/logout.php" class="logout">Cerrar Sesión</a>
        </nav>
    </aside>

    <!-- CONTENIDO -->
    <main class="content">
        <div class="top-row">
            <h1>Lista de Pacientes</h1>
            <a href="patient_new.php" class="btn-primary">+ Añadir Nuevo Paciente</a>
        </div>

        <form method="GET" action="patients.php" class="search-bar">
            <input type="text" name="search" placeholder="Buscar por nombre o DNI..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit">Buscar</button>
        </form>

        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre completo</th>
                    <th>DNI</th>
                    <th>Fecha nacimiento</th>
                    <th>Estado</th>
                    <th style="width:220px;">Acciones</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['full_name']) ?></td>
                            <td><?= htmlspecialchars($row['dni']) ?></td>
                            <td><?= htmlspecialchars($row['birth_date']) ?></td>
                            <td><?= htmlspecialchars($row['status']) ?></td>
                            <td>
                                <a href="patient_view.php?id=<?= $row['id'] ?>" class="link">Ver perfil</a> |
                                <a href="patient_edit.php?id=<?= $row['id'] ?>" class="link">Editar</a> |
                                <a href="patient_delete.php?id=<?= $row['id'] ?>" class="link link-danger"
                                   onclick="return confirm('¿Seguro que quieres eliminar este paciente y todo su historial?');">
                                   Eliminar
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6">No hay pacientes registrados.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

</body>
</html>
