<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

// Verificar que es médico
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'doctor') {
    header("Location: ../auth/index.php");
    exit;
}

/* ESTADÍSTICAS */

$today = date("Y-m-d");

// Total pacientes
$total_stmt = $conn->prepare("SELECT COUNT(*) AS total FROM patients");
$total_stmt->execute();
$total = $total_stmt->get_result()->fetch_assoc()['total'];

// Nuevos hoy
$new_stmt = $conn->prepare("SELECT COUNT(*) AS total FROM patients WHERE DATE(created_at) = ?");
$new_stmt->bind_param("s", $today);
$new_stmt->execute();
$new_today = $new_stmt->get_result()->fetch_assoc()['total'];

// Pendientes
$pending_stmt = $conn->prepare("SELECT COUNT(*) AS total FROM patients WHERE status='revisión' OR status='pendiente'");
$pending_stmt->execute();
$pending = $pending_stmt->get_result()->fetch_assoc()['total'];

// Citas de hoy
$ap_stmt = $conn->prepare("
    SELECT a.*, p.full_name 
    FROM appointments a
    JOIN patients p ON p.id = a.patient_id
    WHERE DATE(a.date) = ?
    ORDER BY a.time ASC
");
$ap_stmt->bind_param("s", $today);
$ap_stmt->execute();
$appointments = $ap_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Médico - MedConnect</title>
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/auth.css">
</head>
<body>

<div class="layout">

    <aside class="sidebar">
        <h2 class="logo">MedConnect</h2>
        <nav>
            <a href="../doctor/doctor_dashboard.php" class="active">Dashboard</a>
            <a href="../doctor/patients.php">Lista de Pacientes</a>
            <a href="../appointments/appointments.php">Citas</a>
            <a href="../auth/logout.php" class="logout">Cerrar Sesión</a>
        </nav>
    </aside>

    <main class="content">

        <h1>Bienvenido, Dr. <?= htmlspecialchars($_SESSION['user_name']) ?></h1>

        <div class="cards">
            <div class="card"><h3>Pacientes Activos</h3><p><?= $total ?></p></div>
            <div class="card"><h3>Nuevos Hoy</h3><p><?= $new_today ?></p></div>
            <div class="card"><h3>Pendientes</h3><p><?= $pending ?></p></div>
        </div>

        <h2>Próximas Citas</h2>
        <div class="appointments">
            <?php if ($appointments->num_rows == 0): ?>
                <p>No hay citas para hoy.</p>
            <?php else: ?>
                <?php while ($ap = $appointments->fetch_assoc()): ?>
                    <div class="appointment-item">
                        <div class="icon">👤</div>
                        <div class="text">
                            <h4><?= htmlspecialchars($ap['full_name']) ?></h4>
                            <p><?= htmlspecialchars($ap['reason']) ?></p>
                        </div>
                        <span><?= substr($ap['time'],0,5) ?></span>
                        <a class="link" href="../doctor/patient_view.php?id=<?= $ap['patient_id'] ?>">Ver detalles</a>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>

    </main>

</div>




<!-- CHAT IA GEMINI -->

<!-- Botón flotante -->
<div id="btn-ia" onclick="toggleChatIA()">💬 IA Médica</div>

<!-- Contenedor del chat -->
<div id="ia-chat">
    <div id="chat-header">Asistente IA Médica</div>
    <div id="chat-box"></div>

    <div id="chat-input-area">
        <textarea id="chat-input" placeholder="Describe el síntoma o pregunta médica..."></textarea>
        <button onclick="sendMessage()">➤</button>
    </div>
</div>

<style>
/* Botón flotante */
#btn-ia {
    position: fixed;
    bottom: 20px; right: 20px;
    background: #2563eb;
    padding: 12px 18px;
    color: white;
    border-radius: 30px;
    cursor: pointer;
    font-weight: bold;
    z-index: 9999;
    box-shadow: 0 6px 20px rgba(0,0,0,0.25);
}

#ia-chat {
    width: 330px;
    height: 450px;
    background: white;
    position: fixed;
    bottom: 80px; right: 20px;
    display: none;
    flex-direction: column;
    border-radius: 12px;
    border: 1px solid #ccc;
    box-shadow: 0 8px 25px rgba(0,0,0,0.35);
    z-index: 9999;
}

#chat-header {
    background: #2563eb;
    color: white;
    padding: 12px;
    text-align: center;
    font-weight: bold;
}

#chat-box {
    flex: 1;
    padding: 10px;
    overflow-y: auto;
    font-size: 14px;
}

.user {
    background: #d1ffd1;
    padding: 8px;
    margin: 6px 0;
    border-radius: 8px;
    text-align: right;
}

.bot {
    background: #e8e8ff;
    padding: 8px;
    margin: 6px 0;
    border-radius: 8px;
    text-align: left;
}

#chat-input-area {
    display: flex;
    border-top: 1px solid #ddd;
}

#chat-input {
    flex: 1;
    height: 60px;
    resize: none;
    border: none;
    padding: 10px;
}

#chat-input-area button {
    width: 20%;
    background: #2563eb;
    border: none;
    color: white;
    cursor: pointer;
}
</style>




<script>
const API_KEY = "AIzaSyDDubi9kyhYDS68HVnwIH_nes9vsH6YIOI";

function toggleChatIA() {
    const box = document.getElementById("ia-chat");
    box.style.display = box.style.display === "flex" ? "none" : "flex";
}

function addMessage(text, type) {
    const div = document.createElement("div");
    div.className = type;
    div.innerText = text;
    document.getElementById("chat-box").appendChild(div);
    const chatBox = document.getElementById("chat-box");
    chatBox.scrollTop = chatBox.scrollHeight;
}

async function sendMessage() {
    const input = document.getElementById("chat-input");
    const message = input.value.trim();
    if (!message) return;

    addMessage(message, "user");
    input.value = "";

    try {
        const response = await fetch(
            "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" + API_KEY,
            {
                method: "POST",
                headers: {"Content-Type": "application/json"},
                body: JSON.stringify({
                    contents: [{ role: "user", parts: [{ text: message }]}]
                })
            }
        );

        const data = await response.json();

        if (data.candidates) {
            addMessage(data.candidates[0].content.parts[0].text, "bot");
        } else {
            addMessage("⚠ Error: " + JSON.stringify(data), "bot");
        }
    } catch (e) {
        addMessage("❌ Error al conectar con la IA", "bot");
    }
}
</script>

</body>
</html>
