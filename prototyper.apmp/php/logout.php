<?php
// Define que a resposta será em JSON
header('Content-Type: application/json');

// Permite requisições de qualquer origem (útil para desenvolvimento local)
header('Access-Control-Allow-Origin: *'); 

// Define os métodos HTTP permitidos para este endpoint
header('Access-Control-Allow-Methods: POST, OPTIONS'); 

// Define os cabeçalhos que podem ser usados na requisição
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Se a requisição for do tipo OPTIONS (pré-voo do CORS), apenas retorna 200 OK
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Inicia a sessão para poder destruí-la
    session_start();
    
    // Destrói todas as variáveis de sessão
    session_unset();
    
    // Destrói a sessão
    session_destroy();
    
    // Retorna uma resposta de sucesso para o frontend
    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'Sessão encerrada com sucesso.']);
} else {
    // Retorna um erro se o método não for POST
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método de requisição não permitido.']);
}
?>