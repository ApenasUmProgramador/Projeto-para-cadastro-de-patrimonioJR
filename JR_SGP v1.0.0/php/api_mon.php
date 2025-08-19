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

$host = 'localhost';
$dbname = 'jr';
$user = 'root';
$password = '';
$method = $_SERVER['REQUEST_METHOD'];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        $tipo = $input['tipo'] ?? '';
        $data = $input['data'] ?? [];
        $id = $input['id'] ?? null;
        $tabelasPermitidas = ['pcs' => 'n_pcs', 'monitor' => 'n_monitor', 'nobreak' => 'n_nobreak'];
        if (!array_key_exists($tipo, $tabelasPermitidas)) { throw new Exception("Tipo inválido para escrita."); }
        if (!$id) {
            if (empty($tipo) || empty($data)) { throw new Exception("Dados insuficientes para criação."); }
            $columns = implode(', ', array_keys($data));
            $placeholders = implode(', ', array_fill(0, count($data), '?'));
            $sql = "INSERT INTO {$tipo} ({$columns}) VALUES ({$placeholders})";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array_values($data));
            echo json_encode(['success' => true, 'message' => 'Registro criado com sucesso!']);
        } else {
            if (empty($tipo) || empty($id) || empty($data)) { throw new Exception("Dados insuficientes para atualização."); }
            $primaryKey = $tabelasPermitidas[$tipo];
            $fields = [];
            $params = [];
            foreach ($data as $key => $value) {
                $fields[] = "{$key} = ?";
                $params[] = $value;
            }
            $params[] = $id;
            $sql = "UPDATE {$tipo} SET " . implode(', ', $fields) . " WHERE {$primaryKey} = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            echo json_encode(['success' => true, 'message' => 'Registro atualizado com sucesso!']);
        }
    } 
    else if ($method === 'GET') {
        $tabelasPermitidas = ['pcs', 'monitor', 'nobreak'];
        if (isset($_GET['getschema'])) {
            $tipo = $_GET['getschema'];
            if (in_array($tipo, $tabelasPermitidas)) {
                $stmt = $pdo->query("DESCRIBE {$tipo}");
                $schema = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode($schema);
            } else { http_response_code(400); echo json_encode(['error' => 'Tabela não permitida.']); }
        }
        else if (isset($_GET['getitem']) && isset($_GET['id'])) {
            $tipo = $_GET['getitem'];
            $id = $_GET['id'];
            $primaryKeyMap = ['pcs' => 'n_pcs', 'monitor' => 'id_monitor', 'nobreak' => 'id_nobreak'];
            if (array_key_exists($tipo, $primaryKeyMap)) {
                $primaryKey = $primaryKeyMap[$tipo];
                $stmt = $pdo->prepare("SELECT * FROM {$tipo} WHERE {$primaryKey} = ?");
                $stmt->execute([$id]);
                $item = $stmt->fetch(PDO::FETCH_ASSOC);
                echo json_encode($item);
            } else { http_response_code(400); echo json_encode(['error' => 'Tipo de registro inválido.']); }
        }
        else if (isset($_GET['registros'])) { 
            $tipo = $_GET['registros'];
            if (in_array($tipo, $tabelasPermitidas)) {
                $stmt = $pdo->query("SELECT * FROM {$tipo}");
                $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode(['registros' => $resultados]);
            } else { http_response_code(400); echo json_encode(['error' => 'Tipo de registro inválido.']); }
        }
        else { 
            $stmt_pcs = $pdo->query("SELECT COUNT(*) AS total FROM pcs");
            $count_pcs = $stmt_pcs->fetch(PDO::FETCH_ASSOC)['total'];
            $stmt_monitor = $pdo->query("SELECT COUNT(*) AS total FROM monitor");
            $count_monitor = $stmt_monitor->fetch(PDO::FETCH_ASSOC)['total'];
            $stmt_nobreak = $pdo->query("SELECT COUNT(*) AS total FROM nobreak");
            $count_nobreak = $stmt_nobreak->fetch(PDO::FETCH_ASSOC)['total'];
            $data = ['pcs' => (int)$count_pcs, 'monitors' => (int)$count_monitor, 'nobreaks' => (int)$count_nobreak ];
            echo json_encode($data);
        }
    }
} catch (Exception $e) {
    error_log("Erro em api_mon.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erro Crítico no Servidor.']);
}
?>