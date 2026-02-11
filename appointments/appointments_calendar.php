<?php
session_start();
require_once __DIR__ . '/../includes/db.php'; 

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'doctor') {
    header("Location: ../auth/index.php");
    exit;
}

$doctor_id = $_SESSION['user_id'];

// Cálculo de mes/año actual
$year  = isset($_GET['year'])  ? intval($_GET['year'])  : intval(date('Y'));
$month = isset($_GET['month']) ? intval($_GET['month']) : intval(date('n'));

if ($month < 1 || $month > 12) {
    $month = intval(date('n'));
}

$currentTimestamp = mktime(0, 0, 0, $month, 1, $year);

$monthName = [
    1 => 'Enero',
    2 => 'Febrero',
    3 => 'Marzo',
    4 => 'Abril',
    5 => 'Mayo',
    6 => 'Junio',
    7 => 'Julio',
    8 => 'Agosto',
    9 => 'Septiembre',
    10 => 'Octubre',
    11 => 'Noviembre',
    12 => 'Diciembre',
][$month];

$daysInMonth = intval(date('t', $currentTimestamp));
$firstWeekday = intval(date('N', $currentTimestamp)); // 1 (Lunes) - 7 (Domingo)

// Cargar citas del mes
$startDate = date('Y-m-01', $currentTimestamp);
$endDate   = date('Y-m-t', $currentTimestamp);

$stmt = $conn->prepare("
    SELECT a.date, a.time, a.reason, p.full_name
    FROM appointments a
    JOIN patients p ON p.id = a.patient_id
    WHERE a.doctor_id = ?
      AND a.date BETWEEN ? AND ?
    ORDER BY a.date ASC, a.time ASC
");
$stmt->bind_param("iss", $doctor_id, $startDate, $endDate);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

// Agrupar citas por día
$appointmentsByDay = [];
while ($row = $result->fetch_assoc()) {
    $day = intval(date('j', strtotime($row['date'])));
    if (!isset($appointmentsByDay[$day])) {
        $appointmentsByDay[$day] = [];
    }
    $appointmentsByDay[$day][] = $row;
}

// Mes anterior / siguiente

$prevTimestamp = strtotime('-1 month', $currentTimestamp);
$nextTimestamp = strtotime('+1 month', $currentTimestamp);

$prevYear  = intval(date('Y', $prevTimestamp));
$prevMonth = intval(date('n', $prevTimestamp));

$nextYear  = intval(date('Y', $nextTimestamp));
$nextMonth = intval(date('n', $nextTimestamp));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Calendario de Citas - MedConnect</title>
    <link rel="stylesheet" href="../css/auth.css">
    <link rel="stylesheet" href="../css/dashboard.css">
    <style>
        .calendar-container {
            background: white;
            padding: 20px;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }

        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .calendar-header h2 {
            margin: 0;
        }

        .calendar-nav a {
            text-decoration: none;
            padding: 6px 10px;
            border-radius: 8px;
            border: 1px solid #d1d5db;
            color: #374151;
            font-size: 13px;
            background: #f9fafb;
        }

        .calendar-nav a:hover {
            background: #e5efff;
            color: #2563eb;
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 6px;
        }

        .calendar-day-header {
            text-align: center;
            font-weight: 600;
            color: #6b7280;
            font-size: 13px;
            margin-bottom: 4px;
        }

        .calendar-cell {
            min-height: 100px;
            background: #f9fafb;
            border-radius: 10px;
            padding: 6px;
            display: flex;
            flex-direction: column;
            font-size: 12px;
        }

        .calendar-cell.empty {
            background: transparent;
        }

        .calendar-day-number {
            font-weight: 600;
            margin-bottom: 4px;
            color: #374151;
        }

        .calendar-event {
            margin-top: 3px;
            padding: 4px 6px;
            border-radius: 8px;
            background: #e0edff;
            color: #1e40af;
            font-size: 11px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .calendar-event span.time {
            font-weight: bold;
            margin-right: 4px;
        }

        .calendar-legend {
            margin-top: 15px;
            font-size: 12px;
            color: #6b7280;
        }
    </style>
</head>
<body>

<div class="layout">

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <h2 class="logo">MedConnect</h2>
        <nav>
            <a href="doctor_dashboard.php">Dashboard</a>
            <a href="patients.php">Lista de Pacientes</a>
            <a href="appointments.php">Citas</a>
            <a href="appointments_calendar.php" class="active">Calendario</a>
            <a href="logout.php" class="logout">Cerrar Sesión</a>
        </nav>
    </aside>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="content">
        <div class="calendar-container">

            <div class="calendar-header">
                <div class="calendar-nav">
                    <a href="appointments_calendar.php?year=<?= $prevYear ?>&month=<?= $prevMonth ?>">&#8592; Mes anterior</a>
                </div>

                <h2><?= $monthName . ' ' . $year ?></h2>

                <div class="calendar-nav">
                    <a href="appointments_calendar.php?year=<?= $nextYear ?>&month=<?= $nextMonth ?>">Mes siguiente &#8594;</a>
                </div>
            </div>

            <div class="calendar-grid">
                <!-- Cabecera días -->
                <div class="calendar-day-header">Lun</div>
                <div class="calendar-day-header">Mar</div>
                <div class="calendar-day-header">Mié</div>
                <div class="calendar-day-header">Jue</div>
                <div class="calendar-day-header">Vie</div>
                <div class="calendar-day-header">Sáb</div>
                <div class="calendar-day-header">Dom</div>

                <!-- Huecos antes del primer día -->
                <?php for ($i = 1; $i < $firstWeekday; $i++): ?>
                    <div class="calendar-cell empty"></div>
                <?php endfor; ?>

                <!-- Días del mes -->
                <?php for ($day = 1; $day <= $daysInMonth; $day++): ?>
                    <?php
                        $hasAppointments = isset($appointmentsByDay[$day]);
                    ?>
                    <div class="calendar-cell<?= $hasAppointments ? ' has-events' : '' ?>">
                        <div class="calendar-day-number"><?= $day ?></div>

                        <?php if ($hasAppointments): ?>
                            <?php foreach ($appointmentsByDay[$day] as $event): ?>
                                <div class="calendar-event">
                                    <span class="time"><?= substr($event['time'], 0, 5) ?></span>
                                    <span class="patient"><?= htmlspecialchars($event['full_name']) ?></span>
                                    <?php if (!empty($event['reason'])): ?>
                                        – <?= htmlspecialchars($event['reason']) ?>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                <?php endfor; ?>
            </div>

            <div class="calendar-legend">
                <p><strong>Nota:</strong> Haz clic en "Citas" en el menú lateral para gestionar (crear/editar/eliminar) las citas.  
                Este calendario muestra visualmente todas las citas programadas para el mes seleccionado.</p>
            </div>

        </div>
    </main>
</div>

</body>
</html>
