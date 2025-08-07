<?php
header('Content-Type: text/html; charset=UTF-8');

echo "<h1>Teste de Verificação de Hash - Definitivo</h1>";

// --- Configurações do Banco de Dados ---
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'jr');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color: green;'>[SUCESSO] Conexão com o banco de dados 'jr' estabelecida.</p>";

    // 1. Defina a senha e o usuário para o teste
    $senha_teste = '1234';
    $usuario_teste = 'dan';
    
    echo "<p>Testando a senha: <b>'{$senha_teste}'</b> para o usuário: <b>'{$usuario_teste}'</b>.</p>";

    // 2. Gere um novo hash para a senha de teste
    $novo_hash = password_hash($senha_teste, PASSWORD_DEFAULT);
    echo "<p>Hash gerado: <b>{$novo_hash}</b></p>";
    
    // 3. Atualize o banco de dados com este novo hash para o usuário 'dan'
    $stmt = $pdo->prepare("UPDATE usuarios SET senha = :hash WHERE Usuario = :username");
    $stmt->execute([
        ':hash' => $novo_hash,
        ':username' => $usuario_teste
    ]);
    
    echo "<p style='color: green;'>[SUCESSO] Banco de dados atualizado com o novo hash.</p>";

    // 4. Verifique a senha 
    // Busque o hash que acabamos de inserir
    $stmt = $pdo->prepare("SELECT senha FROM usuarios WHERE Usuario = :username");
    $stmt->execute([':username' => $usuario_teste]);
    $user_data = $stmt->fetch();

    if ($user_data && password_verify($senha_teste, $user_data['senha'])) {
        echo "<h1 style='color: green;'>[RESULTADO FINAL] SUCESSO! O hash no DB é compatível com a senha.</h1>";
        echo "<p>O problema estava na cópia manual do hash.</p>";
        echo "<p>Você agora pode usar a senha '1234' para fazer login.</p>";
    } else {
        echo "<h1 style='color: red;'>[RESULTADO FINAL] FALHA! O hash no DB NÃO é compatível com a senha.</h1>";
        echo "<p>Isso indica um problema mais profundo com o banco de dados ou a configuração do PHP.</p>";
        echo "<p>Por favor, verifique se a coluna 'senha' na tabela 'usuarios' é do tipo <b>VARCHAR(255)</b>.</p>";
    }

} catch (PDOException $e) {
    echo "<h1 style='color: red;'>[FALHA FATAL] Erro ao executar o teste.</h1>";
    echo "<p>Verifique o erro de conexão: " . $e->getMessage() . "</p>";
}
?>