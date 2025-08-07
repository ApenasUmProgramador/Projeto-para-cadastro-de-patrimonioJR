<?php
// Define o tipo de conteúdo para HTML para que a saída seja formatada no navegador
header('Content-Type: text/html; charset=UTF-8');

echo "<h1>Teste de Conexão com o Banco de Dados</h1>";

// --- Configurações do Banco de Dados ---
$db_host = 'localhost';
$db_user = 'root'; // Usuário do seu banco de dados
$db_pass = '';     // Senha do seu banco de dados (geralmente vazia no Wamp)
$db_name = 'jr';   // Nome do seu banco de dados

// Tenta conectar usando a extensão PDO (PHP Data Objects)
try {
    // Cria uma nova instância de PDO
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    
    // Configura o PDO para lançar exceções em caso de erro
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Se chegou aqui, a conexão foi bem-sucedida
    echo "<p style='color: green; font-weight: bold;'>[SUCESSO] Conexão com o banco de dados '{$db_name}' estabelecida com êxito!</p>";
    echo "<p>Versão do servidor MySQL: " . $pdo->query('select version()')->fetchColumn() . "</p>";

    // Você pode adicionar mais testes aqui, por exemplo, verificar se a tabela existe
    try {
        $pdo->query("SELECT 1 FROM usuarios LIMIT 1");
        echo "<p style='color: green;'>[SUCESSO] A tabela 'usuarios' existe e pode ser acessada.</p>";
    } catch (PDOException $e) {
        echo "<p style='color: orange;'>[AVISO] A tabela 'usuarios' não foi encontrada. Erro: " . $e->getMessage() . "</p>";
    }

} catch (PDOException $e) {
    // Se a conexão falhou, a exceção é capturada aqui
    echo "<p style='color: red; font-weight: bold;'>[FALHA] Não foi possível conectar ao banco de dados.</p>";
    echo "<p style='color: red;'>Detalhes do erro: " . $e->getMessage() . "</p>";
    echo "<p style='color: red;'>Verifique as seguintes configurações:</p>";
    echo "<ul>";
    echo "<li>Seu WampServer está totalmente iniciado (ícone verde)?</li>";
    echo "<li>As credenciais (usuário, senha) e o nome do banco de dados estão corretos?</li>";
    echo "<li>O banco de dados '{$db_name}' realmente existe?</li>";
    echo "</ul>";
}
?>