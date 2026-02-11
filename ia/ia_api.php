<?php
header("Content-Type: application/json");

// Validar mensaje
if (!isset($_POST["mensaje"]) || trim($_POST["mensaje"]) === "") {
    echo json_encode(["error" => "No hay mensaje"]);
    exit;
}

$mensaje = $_POST["mensaje"];

// API KEY GOOGLE GEMINI
$api_key = "AIzaSyDDubi9kyhYDS68HVnwIH_nes9vsH6YIOI";

// URL de Gemini
$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=$api_key";

// Datos para el modelo
$data = [
    "contents" => [
        [
            "parts" => [
                ["text" => "Eres un asistente médico experto. Responde claro y con seguridad.\nPregunta: $mensaje"]
            ]
        ]
    ]
];

$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
    CURLOPT_POSTFIELDS => json_encode($data)
]);

$response = curl_exec($curl);
curl_close($curl);

echo $response;
