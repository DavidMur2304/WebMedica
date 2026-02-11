<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'nurse') {
    header("Location: ../auth/index.php");
    exit;
}

// texto de búsqueda
$search = trim($_GET['search'] ?? '');
$search_param = "%" . $search . "%";

/* LISTADO GENERAL + BÚSQUEDA */
if ($search !== "") {
    $stmt = $conn->prepare("
        SELECT 
            nr.record_date,
            nr.type,
            nr.description,
            p.id AS patient_id,
            p.full_name AS patient_name,
            p.dni,
            u.name AS nurse_name
        FROM nurse_records nr
        JOIN patients p ON p.id = nr.patient_id
        JOIN users u ON u.id = nr.nurse_id
        WHERE p.full_name LIKE ? OR p.dni LIKE ?
        ORDER BY nr.record_date DESC
    ");
    $stmt->bind_param("ss", $search_param, $search_param);
} else {
    $stmt = $conn->prepare("
        SELECT 
            nr.record_date,
            nr.type,
            nr.description,
            p.id AS patient_id,
            p.full_name AS patient_name,
            p.dni,
            u.name AS nurse_name
        FROM nurse_records nr
        JOIN patients p ON p.id = nr.patient_id
        JOIN users u ON u.id = nr.nurse_id
        ORDER BY nr.record_date DESC
    ");
}

$stmt->execute();
$records = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pacientes Atendidos - MedConnect</title>
    <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body>

<div class="layout">

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <h2 class="logo">MedConnect</h2>
        <nav>
            <a href="../nurse/nurse_dashboard.php">Panel Enfermería</a>
            <a href="../nurse/nurse_record_new.php">Nuevo Registro</a>
            <a href="../nurse/nurse_patients_attended.php" class="active">Pacientes Atendidos</a>
            <a href="../auth/logout.php" class="logout">Cerrar Sesión</a>
        </nav>
    </aside>

    <!-- CONTENIDO -->
    <main class="content">
        <h1>Pacientes Atendidos</h1>

        <!-- 🔍 BUSCADOR -->
        <form method="GET" style="margin-bottom:20px; display:flex; gap:10px;">
            <input 
                type="text"
                name="search"
                placeholder="Buscar por nombre o DNI..."
                value="<?= htmlspecialchars($search) ?>"
                style="flex:1; padding:10px; border-radius:8px; border:1px solid #d1d5db;"
            >
            <button class="btn-primary">Buscar</button>
        </form>

        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Paciente</th>
                        <th>DNI</th>
                        <th>Enfermero/a</th>
                        <th>Tipo</th>
                        <th>Descripción</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>

                <?php if ($records->num_rows > 0): ?>
                    <?php while ($r = $records->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($r['record_date']) ?></td>
                            <td><?= htmlspecialchars($r['patient_name']) ?></td>
                            <td><?= htmlspecialchars($r['dni']) ?></td>
                            <td><?= htmlspecialchars($r['nurse_name']) ?></td>
                            <td><?= htmlspecialchars($r['type']) ?></td>
                            <td><?= htmlspecialchars($r['description']) ?></td>
                            <td>
                                <a href="../nurse/nurse_patient_view.php?id=<?= $r['patient_id'] ?>" class="link">
                                    Ver Perfil
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">No se encontraron resultados.</td>
                    </tr>
                <?php endif; ?>

                </tbody>
            </table>
        </div>
    </main>

</div>

</body>
</html>
