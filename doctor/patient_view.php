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

/* DATOS DEL PACIENTE */
$stmt = $conn->prepare("SELECT * FROM patients WHERE id = ?");
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$patient = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$patient) {
    header("Location: ../doctor/patients.php");
    exit;
}

/* CONSULTAS MÉDICAS */
$stmt = $conn->prepare("
    SELECT id, reason, diagnosis, treatment, notes, visit_date 
    FROM consultations 
    WHERE patient_id = ?
    ORDER BY visit_date DESC
");
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$consultations = $stmt->get_result();
$stmt->close();

/* INFORMES */
$stmt = $conn->prepare("SELECT * FROM reports WHERE patient_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$reports = $stmt->get_result();
$stmt->close();

/* REGISTROS DE ENFERMERÍA */
$stmt = $conn->prepare("SELECT * FROM nurse_records WHERE patient_id = ? ORDER BY record_date DESC");
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$nurse_records = $stmt->get_result();
$stmt->close();

/* CITAS DEL PACIENTE */
$stmt = $conn->prepare("
    SELECT a.*, u.name AS doctor_name 
    FROM appointments a
    JOIN users u ON u.id = a.doctor_id
    WHERE a.patient_id = ?
    ORDER BY a.date DESC, a.time DESC
");
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$appointments = $stmt->get_result();
$stmt->close();

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Perfil Paciente - MedConnect</title>
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/auth.css">

    <style>
        .tab-content-section { display: none; }
        .tab-content-section.active { display: block; }
        .history-box {
            background:white;
            padding:15px;
            border-radius:12px;
            margin-bottom:15px;
            box-shadow:0 3px 10px rgba(0,0,0,0.05);
        }
    </style>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const tabs = document.querySelectorAll(".tab");
            const contents = document.querySelectorAll(".tab-content-section");

            tabs.forEach((tab, index) => {
                tab.addEventListener("click", () => {
                    tabs.forEach(t => t.classList.remove("active"));
                    contents.forEach(c => c.classList.remove("active"));

                    tab.classList.add("active");
                    contents[index].classList.add("active");
                });
            });
        });
    </script>
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
        <h1>Perfil del Paciente</h1>

        <div class="patient-header">
            <div>
                <h2><?= htmlspecialchars($patient['full_name']) ?></h2>
                <p>DNI: <?= htmlspecialchars($patient['dni']) ?></p>
                <p>Estado: <?= htmlspecialchars($patient['status']) ?></p>
            </div>

            <div>
                <a href="../appointments/appointment_new.php?patient_id=<?= $patient_id ?>" class="btn-primary">+ Nueva Cita</a>
                <a href="../reports/patient_pdf.php?id=<?= $patient_id ?>" target="_blank" class="btn-secondary">Descargar PDF</a>
            </div>
        </div>
        <!-- TABS -->
        <div class="tabs">
            <button class="tab active">Resumen</button>
            <button class="tab">Historial Clínico</button>
            <button class="tab">Consultas</button>
            <button class="tab">Informes</button>
            <button class="tab">Curas / Enfermería</button>
            <button class="tab">Citas</button>
        </div>

        <!-- RESUMEN -->
        <div class="tab-content-section active">
            <h2>Resumen del Paciente</h2>
            <p><strong>Nombre:</strong> <?= htmlspecialchars($patient['full_name']) ?></p>
            <p><strong>Fecha de nacimiento:</strong> <?= htmlspecialchars($patient['birth_date']) ?></p>
            <p><strong>Género:</strong> <?= htmlspecialchars($patient['gender']) ?></p>
            <p><strong>Teléfono:</strong> <?= htmlspecialchars($patient['phone']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($patient['email']) ?></p>
            <p><strong>Dirección:</strong> <?= htmlspecialchars($patient['address']) ?></p>
        </div>

        <!--HISTORIAL CLÍNICO -->
        <div class="tab-content-section">
            <h2>Historial Clínico</h2>

            <?php if ($consultations->num_rows === 0): ?>
                <p>No hay historial clínico registrado.</p>

            <?php else: ?>
                <?php while ($c = $consultations->fetch_assoc()): ?>
                    <div class="history-box">
                        <h3><?= date("d/m/Y", strtotime($c['visit_date'])) ?></h3>
                        <p><strong>Motivo:</strong> <?= htmlspecialchars($c['reason']) ?></p>
                        <p><strong>Diagnóstico:</strong> <?= htmlspecialchars($c['diagnosis']) ?></p>
                        <p><strong>Tratamiento:</strong> <?= htmlspecialchars($c['treatment']) ?></p>
                        <p><strong>Notas:</strong><br><?= nl2br(htmlspecialchars($c['notes'])) ?></p>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>

        <!-- CONSULTAS -->
        <div class="tab-content-section">
            <div class="top-row">
                <h2>Consultas Médicas</h2>
                <a href="../consultations/consultation_new.php?patient_id=<?= $patient_id ?>" class="btn-primary">+ Nueva Consulta</a>
            </div>

            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Motivo</th>
                        <th>Diagnóstico</th>
                        <th>Tratamiento</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    // Re-ejecutar porque la consulta anterior consumió el cursor
                    $stmt = $conn->prepare("
                        SELECT * FROM consultations WHERE patient_id = ? ORDER BY visit_date DESC
                    ");
                    $stmt->bind_param("i", $patient_id);
                    $stmt->execute();
                    $consulta_table = $stmt->get_result();

                    while ($c = $consulta_table->fetch_assoc()): ?>
                        <tr>
                            <td><?= $c['visit_date'] ?></td>
                            <td><?= htmlspecialchars($c['reason']) ?></td>
                            <td><?= htmlspecialchars($c['diagnosis']) ?></td>
                            <td><?= htmlspecialchars($c['treatment']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- INFORMES -->
        <div class="tab-content-section">
            <div class="top-row">
                <h2>Informes Médicos</h2>
                <a href="../reports/report_new.php?patient_id=<?= $patient_id ?>" class="btn-primary">+ Nuevo Informe</a>
            </div>

            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Título</th>
                        <th>Contenido</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php while ($r = $reports->fetch_assoc()): ?>
                        <tr>
                            <td><?= $r['created_at'] ?></td>
                            <td><?= htmlspecialchars($r['title']) ?></td>
                            <td><?= nl2br(htmlspecialchars($r['content'])) ?></td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- REGISTROS DE ENFERMERÍA -->
        <div class="tab-content-section">
            <div class="top-row">
                <h2>Registros de Enfermería</h2>
            </div>

            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Descripción</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php while ($n = $nurse_records->fetch_assoc()): ?>
                        <tr>
                            <td><?= $n['record_date'] ?></td>
                            <td><?= htmlspecialchars($n['type']) ?></td>
                            <td><?= nl2br(htmlspecialchars($n['description'])) ?></td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- CITAS -->
        <div class="tab-content-section">
            <div class="top-row">
                <h2>Citas del Paciente</h2>
                <a href="../appointments/appointment_new.php?patient_id=<?= $patient_id ?>" class="btn-primary">+ Nueva Cita</a>
            </div>

            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Médico</th>
                        <th>Motivo</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php while ($a = $appointments->fetch_assoc()): ?>
                        <tr>
                            <td><?= $a['date'] ?></td>
                            <td><?= $a['time'] ?></td>
                            <td><?= htmlspecialchars($a['doctor_name']) ?></td>
                            <td><?= htmlspecialchars($a['reason']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>
</div>

</body>
</html>
