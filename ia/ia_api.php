<?php
header("Content-Type: application/json; charset=UTF-8");

// Probar primero .env.php y luego .env
$envPhpPath = __DIR__ . '/../.env.php';
$envPath = __DIR__ . '/../.env';

if (file_exists($envPhpPath)) {
    require_once $envPhpPath;
} elseif (file_exists($envPath)) {
    require_once $envPath;
} else {
    echo json_encode([
        "error" => true,
        "message" => "No se encontró .env.php ni .env"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!defined('GEMINI_API_KEY') || empty(GEMINI_API_KEY)) {
    echo json_encode([
        "error" => true,
        "message" => "API key no configurada"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$api_key = trim(GEMINI_API_KEY);

if (!isset($_POST["mensaje"]) || trim($_POST["mensaje"]) === "") {
    echo json_encode([
        "error" => true,
        "message" => "No hay mensaje"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$mensaje = trim($_POST["mensaje"]);

$prompt = "Eres un asistente médico informativo. " .
          "Responde de forma clara y breve. " .
          "No hagas diagnósticos definitivos. " .
          "Si los síntomas parecen graves, recomienda acudir a un médico. " .
          "Pregunta del usuario: " . $mensaje;

$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . urlencode($api_key);

$data = [
    "contents" => [
        [
            "parts" => [
                ["text" => $prompt]
            ]
        ]
    ]
];

$curl = curl_init();

curl_setopt_array($curl, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json"
    ],
    CURLOPT_POSTFIELDS => json_encode($data, JSON_UNESCAPED_UNICODE),
    CURLOPT_TIMEOUT => 30
]);

$response = curl_exec($curl);
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
$curlError = curl_error($curl);

curl_close($curl);

if ($response === false) {
    echo json_encode([
        "error" => true,
        "message" => "Error conectando con la API",
        "detail" => $curlError
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$result = json_decode($response, true);

if ($httpCode !== 200 || isset($result["error"])) {
    $mensajeError = $result["error"]["message"] ?? "Error desconocido de la API";

    echo json_encode([
        "error" => true,
        "message" => $mensajeError
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$respuesta = $result["candidates"][0]["content"]["parts"][0]["text"] ?? null;

if (!$respuesta) {
    echo json_encode([
        "error" => true,
        "message" => "La IA no devolvió respuesta"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode([
    "error" => false,
    "respuesta" => $respuesta
], JSON_UNESCAPED_UNICODE);