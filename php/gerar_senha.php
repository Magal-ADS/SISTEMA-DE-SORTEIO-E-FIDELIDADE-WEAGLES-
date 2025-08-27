<?php
// Arquivo temporário: php/gerar_senha.php

// A senha que queremos usar
$senhaParaCriptografar = '123456';

// O PHP vai criar uma senha criptografada (hash)
$hash = password_hash($senhaParaCriptografar, PASSWORD_DEFAULT);

// Mostra a senha na tela para podermos copiar
echo "Sua nova senha criptografada é:<br><br>";
echo "<textarea style='width: 100%; font-size: 16px; padding: 10px;' rows='4' cols='80' readonly>" . htmlspecialchars($hash) . "</textarea>";
echo "<br><br>Copie o código gigante acima e siga os passos que te passei para atualizar o banco de dados.";
?>