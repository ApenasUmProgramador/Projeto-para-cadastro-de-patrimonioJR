<?php
header("Access-Control-Allow-Origin: http://localhost");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit(); }

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Acesso não autorizado.']);
    exit(); 
}

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'jr');

try {
    $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8', DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        $newUsername = $input['newUsername'] ?? '';

        if (empty($newUsername)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'O novo nome não pode estar vazio.']);
            exit;
        }

        $userId = $_SESSION['user_id'];

        $stmt = $pdo->prepare("UPDATE usuarios SET Usuario = :username WHERE idacesso = :id");
        $stmt->execute([':username' => $newUsername, ':id' => $userId]);

        $_SESSION['username'] = $newUsername;

        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Nome de usuário alterado com sucesso!', 'newUsername' => $newUsername]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor.']);
    error_log("Erro em api_user.php: " . $e->getMessage());
}
?>