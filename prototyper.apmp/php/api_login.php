<?php
header('Content-Type: application/json');
session_start();

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'jr');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
    exit;
}

try {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    $input = json_decode(file_get_contents('php://input'), true);
    $username_input = $input['username'] ?? ''; 
    $password_input = $input['password'] ?? ''; 

    if (empty($username_input) || empty($password_input)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Usuário e senha são obrigatórios.']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT idacesso, Usuario, senha FROM usuarios WHERE Usuario = :username");
    $stmt->execute([':username' => $username_input]);
    $user = $stmt->fetch();

    if ($user && password_verify($password_input, $user['senha'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['idacesso'];
        $_SESSION['username'] = $user['Usuario'];
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Login bem-sucedido!',
            'username' => $user['Usuario']
        ]);

    } else {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Usuário ou senha inválidos.']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor.']);
    error_log('Erro de login: ' . $e->getMessage());
}
?>