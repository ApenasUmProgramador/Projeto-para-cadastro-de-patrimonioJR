

<?php
// Define que a resposta será em JSON
header('Content-Type: application/json');

// Permite requisições de qualquer origem (útil para desenvolvimento local)
header('Access-Control-Allow-Origin: *'); 

// Define os métodos HTTP permitidos
header('Access-Control-Allow-Methods: GET, POST, OPTIONS'); 

// Define os cabeçalhos que podem ser usados na requisição
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Se a requisição for do tipo OPTIONS (pré-voo do CORS), apenas retorna 200 OK
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Inicia a sessão para armazenar informações do usuário autenticado
session_start();

// --- Configurações do Banco de Dados ---
define('DB_HOST', 'localhost');
define('DB_USER', 'root');   // Seu usuário do banco de dados (Wamp default)
define('DB_PASS', '');       // Sua senha do banco de dados (Wamp default é vazio)
define('DB_NAME', 'jr');     // Nome do seu banco de dados

// --- Função para conectar ao Banco de Dados usando PDO ---
function getDbConnection() {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch (PDOException $e) {
        error_log('Erro de conexão com o banco de dados: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erro interno do servidor ao conectar ao DB.']);
        exit();
    }
}

// --- Lógica de Autenticação (handleLogin) ---
// ... (código de cabeçalhos e funções de conexão) ...

// --- Lógica de Autenticação (handleLogin) ---
function handleLogin($pdo) {
    $username = $_POST['username'] ?? ''; 
    $password = $_POST['password'] ?? ''; 

    if (empty($username) || empty($password)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Usuário e senha são obrigatórios.']);
        return;
    }

    $stmt = $pdo->prepare("SELECT Usuario, senha FROM usuarios WHERE Usuario = :username");
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['senha'])) {
        // ... (lógica da sessão) ...
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Login bem-sucedido!',
            'username' => $user['Usuario'] // Linha corrigida: retorna o nome do usuário
        ]);
    } else {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Usuário ou senha inválidos.']);
    }
}

// --- Roteamento da API ---
$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

switch ($action) {
    case 'login_attempt':
        if ($method === 'POST') {
            $pdo = getDbConnection();
            handleLogin($pdo);
        } else {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método não permitido para esta ação.']);
        }
        break;

    case 'test_db_connection':
        if ($method === 'GET') {
            testDbConnection();
        } else {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método não permitido para esta ação.']);
        }
        break;
        
    default:
        // Assume que é uma tentativa de login se o método for POST, mesmo sem a action
        if ($method === 'POST') {
            $pdo = getDbConnection();
            handleLogin($pdo);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Ação não encontrada ou método inválido.']);
        }
        break;
}
?>
