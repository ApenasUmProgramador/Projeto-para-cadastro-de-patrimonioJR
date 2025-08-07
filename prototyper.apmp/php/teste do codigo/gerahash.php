<?php
// Define a senha que você quer usar para o login.
// Você pode alterar '1234' para qualquer senha que desejar.
$senha_para_hashear = '1234'; 

// Gera o hash da senha usando o algoritmo padrão (recomendado)
$hash_gerado = password_hash($senha_para_hashear, PASSWORD_DEFAULT);

// Exibe a senha em texto puro e o hash gerado para que você possa copiá-lo
echo "Senha em texto puro: " . $senha_para_hashear . "<br>";
echo "<b>Hash para copiar e colar no banco de dados:</b><br>";
echo "<b>" . $hash_gerado . "</b>";

// Lembre-se:
// 1. Copie o texto em negrito acima (o hash completo).
// 2. Vá até o seu phpMyAdmin, na tabela 'usuarios'.
// 3. Edite a linha do usuário desejado e cole este hash na coluna 'senha'.
// 4. Salve a alteração e tente fazer o login na sua página.
?>