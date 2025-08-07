<?php
$senha_digitada_no_form = '1234'; // SUBSTITUA pela senha que você digita na tela de login (ex: '1234')
$hash_copiado_do_banco = '$2y$10$QdlSV/uubBL1zIdreE'; // SUBSTITUA pelo hash que você copiou do phpMyAdmin

// Tenta verificar a senha
if (password_verify($senha_digitada_no_form, $hash_copiado_do_banco)) {
    echo "<h1 style='color: green;'>[SUCESSO] A senha e o hash são compatíveis!</h1>";
    echo "Você pode fazer login com essa senha.";
} else {
    echo "<h1 style='color: red;'>[FALHA] A senha e o hash NÃO são compatíveis.</h1>";
    echo "O hash no banco de dados está incorreto ou a senha que você digitou está errada.";
}
?>