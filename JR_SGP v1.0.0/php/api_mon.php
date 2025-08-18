<?php
// --- CONFIGURAÇÕES DO BANCO DE DADOS ---
$host = 'localhost';
$dbname = 'jr';
$user = 'root';
$password = '';

// --- INICIALIZAÇÃO E CABEÇALHOS ---
header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];

try {
    // --- CONEXÃO COM O BANCO DE DADOS ---
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // =============================================================
    // ROTA PARA CRIAR OU ATUALIZAR UM REGISTRO (MÉTODO POST)
    // =============================================================
    if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        $tipo = $input['tipo'] ?? '';
        $data = $input['data'] ?? [];
        $id = $input['id'] ?? null; // ID só existe em updates

        // Lista de tabelas e chaves primárias permitidas
        $tabelasPermitidas = ['pcs' => 'n_pcs', 'monitor' => 'id_monitor', 'nobreack' => 'id_nobreack'];
        if (!array_key_exists($tipo, $tabelasPermitidas)) {
            throw new Exception("Tipo de registro inválido para escrita.");
        }

        // --- LÓGICA DE CRIAÇÃO (INSERT) ---
        if (!$id) {
            if (empty($tipo) || empty($data)) {
                throw new Exception("Dados insuficientes para criação.");
            }

            // Validação de campo único (exemplo para 'nome_maquina')
            if ($tipo === 'pcs' && !empty($data['nome_maquina'])) {
                $stmt = $pdo->prepare("SELECT n_pcs FROM pcs WHERE nome_maquina = ?");
                $stmt->execute([$data['nome_maquina']]);
                if ($stmt->fetch()) {
                    http_response_code(409); // Conflict
                    echo json_encode(['error' => 'O nome da máquina "' . htmlspecialchars($data['nome_maquina']) . '" já existe.']);
                    exit;
                }
            }

            $columns = implode(', ', array_keys($data));
            $placeholders = implode(', ', array_fill(0, count($data), '?'));
            $sql = "INSERT INTO {$tipo} ({$columns}) VALUES ({$placeholders})";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array_values($data));

            echo json_encode(['success' => true, 'message' => 'Registro criado com sucesso!']);
        }
        // --- LÓGICA DE ATUALIZAÇÃO (UPDATE) ---
        else {
            if (empty($tipo) || empty($id) || empty($data)) {
                throw new Exception("Dados insuficientes para atualização.");
            }
            
            $primaryKey = $tabelasPermitidas[$tipo];

            // Constrói a query de UPDATE
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
    // =============================================================
    // ROTA PARA BUSCAR DADOS (MÉTODO GET)
    // =============================================================
    else if ($method === 'GET') {
        $tabelasPermitidas = ['pcs', 'monitor', 'nobreack'];

        // CASO 1: Descreve a estrutura da tabela (para criar formulários dinâmicos)
        if (isset($_GET['getschema'])) {
            $tipo = $_GET['getschema'];
            if (in_array($tipo, $tabelasPermitidas)) {
                $stmt = $pdo->query("DESCRIBE {$tipo}");
                $schema = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode($schema);
            } else {
                http_response_code(400); echo json_encode(['error' => 'Tabela não permitida.']);
            }
        }
        // CASO 2: Busca um único item por ID (para a página editar.html)
        else if (isset($_GET['getitem']) && isset($_GET['id'])) {
            $tipo = $_GET['getitem'];
            $id = $_GET['id'];
            $primaryKeyMap = ['pcs' => 'n_pcs', 'monitor' => 'id_monitor', 'nobreack' => 'id_nobreack'];
            
            if (array_key_exists($tipo, $primaryKeyMap)) {
                $primaryKey = $primaryKeyMap[$tipo];
                $stmt = $pdo->prepare("SELECT * FROM {$tipo} WHERE {$primaryKey} = ?");
                $stmt->execute([$id]);
                $item = $stmt->fetch(PDO::FETCH_ASSOC);
                echo json_encode($item);
            } else {
                http_response_code(400); echo json_encode(['error' => 'Tipo de registro inválido.']);
            }
        }
        // CASO 3: Busca a lista completa de registros (para a página visualizar.html)
        else if (isset($_GET['registros'])) {
            $tipo = $_GET['registros'];
            if (in_array($tipo, $tabelasPermitidas)) {
                $stmt = $pdo->query("SELECT * FROM {$tipo}");
                $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode($resultados);
            } else {
                http_response_code(400); echo json_encode(['error' => 'Tipo de registro inválido.']);
            }
        }
        // CASO 4 (PADRÃO): Lógica de contagem para o Dashboard (para a página main.html)
        else {
            $stmt_pcs = $pdo->query("SELECT COUNT(*) AS total FROM pcs");
            $count_pcs = $stmt_pcs->fetch(PDO::FETCH_ASSOC)['total'];

            $stmt_monitor = $pdo->query("SELECT COUNT(*) AS total FROM monitor");
            $count_monitor = $stmt_monitor->fetch(PDO::FETCH_ASSOC)['total'];

            $stmt_nobreak = $pdo->query("SELECT COUNT(*) AS total FROM nobreack");
            $count_nobreak = $stmt_nobreak->fetch(PDO::FETCH_ASSOC)['total'];

            $data = [
                'pcs' => (int)$count_pcs,
                'monitors' => (int)$count_monitor,
                'nobreaks' => (int)$count_nobreak
            ];
            echo json_encode($data);
        }
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro Crítico no Servidor: ' . $e->getMessage()]);
}
?>