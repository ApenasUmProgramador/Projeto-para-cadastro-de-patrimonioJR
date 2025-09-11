<?php
header("Access-Control-Allow-Origin: http://localhost");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit(); }

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user_id']) && isset($_SESSION['username'])) {
    http_response_code(200);
    echo json_encode([
        'authenticated' => true,
        'username' => $_SESSION['username']
    ]);
} else {
    http_response_code(401);
    echo json_encode([
        'authenticated' => false,
        'error' => 'Acesso não autorizado. Por favor, faça o login.'
    ]);
}
?>