<?php
$senha_para_hashear = '1234'; // Substitua pela senha que você quer usar
$hash_gerado = password_hash($senha_para_hashear, PASSWORD_DEFAULT);
echo "Use este hash para a senha '1234':<br>";
echo "<b>" . $hash_gerado . "</b>";
?>