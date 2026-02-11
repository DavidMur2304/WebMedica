<?php
session_start();
require_once __DIR__ . "/../includes/db.php";

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role     = trim($_POST['role'] ?? '');

    if ($name === "" || $email === "" || $password === "" || $role === "") {
        $error = "Rellena todos los campos.";
    } else {
        // Existe el email
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "El email ya está registrado.";
        } else {
            // Registrar usuario
            $hashed = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $hashed, $role);
            $stmt->execute();

            $success = "Cuenta creada correctamente. ¡Ya puedes iniciar sesión!";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>MedConnect - Registrarse</title>

    <link rel="stylesheet" href="../css/auth.css">
    <link rel="stylesheet" href="../css/dashboard.css">

    <style>
        body {
            margin: 0;
            font-family: system-ui, sans-serif;
            background: url("../img/fondoLogin.jpg") no-repeat center center fixed;
            background-size: cover;
        }

        .auth-container {
            width: 100%;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .auth-box {
            width: 420px;
            background: rgba(255,255,255,0.90);
            padding: 35px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.20);
            text-align: center;
        }

        .auth-box input,
        .auth-box select {
            width: 100%;
            padding: 12px;
            margin: 8px 0 15px;
            border-radius: 10px;
            border: 1px solid #ccc;
        }

        button {
            width: 100%;
            background: #2563eb;
            padding: 12px;
            border: none;
            color: white;
            font-size: 16px;
            border-radius: 10px;
            cursor: pointer;
        }

        button:hover {
            background: #1d4ed8;
        }

        .alert {
            padding: 10px;
            border-radius: 10px;
            margin-bottom: 15px;
            font-size: 14px;
        }

        .alert.error {
            background: #fee2e2;
            color: #b91c1c;
        }

        .alert.success {
            background: #dcfce7;
            color: #166534;
        }
    </style>
</head>
<body>

<div class="auth-container">
    <div class="auth-box">
        <h1 class="app-title">MedConnect</h1>
        <p class="subtitle">Crear Cuenta Nueva</p>

        <?php if ($error): ?>
            <div class="alert error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <label>Nombre completo</label>
            <input type="text" name="name" placeholder="Ej: Dr. Martínez" required>

            <label>Email</label>
            <input type="email" name="email" placeholder="Introduce tu email" required>

            <label>Contraseña</label>
            <input type="password" name="password" placeholder="Introduce tu contraseña" required>

            <label>Rol</label>
            <select name="role" required>
                <option value="">Selecciona tu rol</option>
                <option value="doctor">Médico</option>
                <option value="nurse">Enfermero/a</option>
            </select>

            <button type="submit">Registrar</button>

            <p class="small">
                ¿Ya tienes cuenta?
                <a href="../auth/index.php">Inicia Sesión</a>
            </p>
        </form>
    </div>
</div>

</body>
</html>
