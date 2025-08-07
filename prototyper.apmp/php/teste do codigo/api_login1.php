<?php
// Define que a resposta será em JSON
header('Content-Type: application/json');

// Permite requisições de qualquer origem (útil para desenvolvimento local)
// Em produção, mude o '*' para o domínio exato do seu frontend.
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
        // Configura o PDO para lançar exceções em caso de erro, facilitando a depuração
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // Retorna os resultados das consultas como arrays associativos (chave => valor)
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch (PDOException $e) {
        // Em caso de erro de conexão, registra o erro no log e envia uma resposta JSON de erro
        error_log('Erro de conexão com o banco de dados: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erro interno do servidor ao conectar ao DB.']);
        exit();
    }
}

// --- Lógica de Autenticação (handleLogin) ---
function handleLogin($pdo) {
    // Puxa os dados das colunas 'Usuario' e 'senha' da tabela 'usuarios'
    // O operador '??' ('null coalescing operator') evita avisos caso os índices não existam
    $username = $_POST['username'] ?? ''; 
    $password = $_POST['password'] ?? ''; 

    // Valida se os campos não estão vazios
    if (empty($username) || empty($password)) {
        http_response_code(400); // Bad Request
        echo json_encode(['success' => false, 'message' => 'Usuário e senha são obrigatórios.']);
        return;
    }

    // Prepara a consulta SQL para evitar injeção SQL
    // Busca pela coluna 'Usuario' na tabela 'usuarios'
    $stmt = $pdo->prepare("SELECT Usuario, senha FROM usuarios WHERE Usuario = :username");
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch();

    // password_verify() é a maneira correta e segura de verificar senhas com hash.
    // Ela compara a senha fornecida pelo usuário com o hash armazenado no banco de dados.
    if ($user && password_verify($password, $user['senha'])) {
        // Login bem-sucedido
        // Armazena o nome de usuário na sessão
        $_SESSION['username'] = $user['Usuario']; 

        http_response_code(200); // OK
        echo json_encode([
            'success' => true,
            'message' => 'Login bem-sucedido!',
        ]);
    } else {
        // Falha no login
        http_response_code(401); // Unauthorized
        echo json_encode(['success' => false, 'message' => 'Usuário ou senha inválidos.']);
    }
}

// --- Nova Função: Teste de Conexão com Banco de Dados ---
function testDbConnection() {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Tenta fazer uma query simples para garantir que a conexão está ativa
        $pdo->query("SELECT 1");
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Conexão com o banco de dados JR estabelecida com sucesso!']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Falha ao estabelecer conexão com o banco de dados JR. Erro: ' . $e->getMessage()]);
    }
}

// --- Roteamento da API ---
$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

switch ($action) {
    case 'login_attempt':
        if ($method === 'POST') {
            $pdo = getDbConnection(); // Obtém a conexão com o banco de dados
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
        // Se a ação não for especificada ou não for reconhecida, assumimos que é uma tentativa de login via POST
        // e tentamos processá-la. Isso é para compatibilidade com o JS que não passa a action na URL.
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