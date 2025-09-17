<?php
// /php/teste_senha.php - Script de Diagnóstico

// --- Configure os dados para o teste ---
// Use a senha que você definiu e o hash que o Heroku gerou para ela
$senha_que_estou_testando = '32426507'; 
$hash_que_deveria_estar_no_banco = '$2y$12$fc21lbYfwGs1A3dw4Js9/OOZj.j7XBmGg4ktiuG0L1w39jJk2H2uW';

echo "<h1>Teste de Verificação de Senha</h1>";
echo "<pre>"; // Usamos <pre> para manter a formatação e facilitar a leitura

echo "INICIANDO TESTE...\n\n";
echo "SENHA SENDO TESTADA: " . htmlspecialchars($senha_que_estou_testando) . "\n";
echo "HASH ESPERADO NO BANCO: " . htmlspecialchars($hash_que_deveria_estar_no_banco) . "\n\n";

echo "EXECUTANDO A FUNÇÃO password_verify()...\n";

// Esta é a função que o seu 'processa_login.php' usa
if (password_verify($senha_que_estou_testando, $hash_que_deveria_estar_no_banco)) {
    echo "\n--> RESULTADO: SUCESSO! A senha BATE com o hash.\n\n";
    echo "CONCLUSÃO: Se este teste funcionou, o problema NÃO é a senha. O arquivo 'processa_login.php' no servidor deve estar desatualizado por algum motivo (cache, etc).";
} else {
    echo "\n--> RESULTADO: FALHA! A senha NÃO BATE com o hash.\n\n";
    echo "CONCLUSÃO: Se este teste falhou, significa que o hash que está no banco de dados está diferente do que geramos. O comando UPDATE pode não ter sido executado corretamente ou o hash foi corrompido.";
}

echo "\n...TESTE FINALIZADO.";
echo "</pre>";
?>