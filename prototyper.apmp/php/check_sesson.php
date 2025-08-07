<?php
// Define que a resposta será em JSON
header('Content-Type: application/json');

// Permite requisições de qualquer origem (útil para desenvolvimento local)
header('Access-Control-Allow-Origin: *');

// Define os métodos HTTP permitidos
header('Access-Control-Allow-Methods: GET, OPTIONS');

// Define os cabeçalhos que podem ser usados na requisição
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Se a requisição for do tipo OPTIONS (pré-voo do CORS), apenas retorna 200 OK
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Inicia a sessão
session_start();

// Verifica se a variável de sessão 'username' está definida
if (isset($_SESSION['username'])) {
    http_response_code(200);
    echo json_encode([
        'authenticated' => true,
        'username' => $_SESSION['username']
    ]);
} else {
    http_response_code(401);
    echo json_encode(['authenticated' => false]);
}
?>