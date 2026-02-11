<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($email === "" || $password === "") {
        $error = "Rellena todos los campos.";
    } else {
        $stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            if (password_verify($password, $user['password'])) {

                $_SESSION['user_id']   = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];

                if ($user['role'] === 'doctor') {
                header("Location: ../doctor/doctor_dashboard.php");
                exit;
                }else {
                 header("Location: ../nurse/nurse_dashboard.php");
                exit;
        }
            } else {
                $error = "Contraseña incorrecta.";
            }
        } else {
            $error = "No existe un usuario con ese email.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>MedConnect - Iniciar Sesión</title>

    <!-- RUTA CORREGIDA -->
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
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .auth-box {
            width: 380px;
            background: white;
            padding: 35px;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            text-align: center;
        }

        .app-title {
            margin-top: 0;
            font-size: 28px;
            color: #1f2937;
        }

        .subtitle {
            color: #374151;
            margin-bottom: 20px;
        }

        .auth-box input {
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

        .alert.error {
            background: #fee2e2;
            padding: 10px;
            border-radius: 10px;
            color: #b91c1c;
            margin-bottom: 15px;
        }

        .small a {
            color: #2563eb;
        }
    </style>
</head>
<body>

<div class="auth-container">
    <div class="auth-box">
        <h1 class="app-title">MedConnect</h1>
        <p class="subtitle">Bienvenido de Nuevo</p>

        <?php if ($error): ?>
            <div class="alert error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <label>Email</label>
            <input type="email" name="email" placeholder="Introduce tu email" required>

            <label>Contraseña</label>
            <input type="password" name="password" placeholder="Introduce tu contraseña" required>

            <button type="submit">Acceder</button>

            <p class="small">
                ¿No tienes cuenta?
                <a href="../auth/register.php">Regístrate</a>
            </p>
        </form>
    </div>
</div>

</body>
</html>
