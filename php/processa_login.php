<?php
// /php/processa_login.php (VERSÃO DE DIAGNÓSTICO)

// MENSAGEM PARA CONFIRMAR QUE O ARQUIVO NOVO ESTÁ RODANDO
echo "<pre>-- SCRIPT DE DEBUG v4 -- INICIADO --</pre>";

if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}
require_once "db_config.php";

$cnpj_formatado = trim($_POST['cnpj'] ?? '');
$senha = trim($_POST['senha'] ?? '');

echo "<pre>CNPJ Recebido (com máscara): " . htmlspecialchars($cnpj_formatado) . "</pre>";
echo "<pre>Senha Recebida: " . htmlspecialchars($senha) . "</pre>";

if (empty($cnpj_formatado) || empty($senha)) {
    echo "<pre>ERRO: Campos vazios. Saindo...</pre>";
    exit;
}

$cnpj_limpo = preg_replace('/[^0-9]/', '', $cnpj_formatado);
echo "<pre>CNPJ Limpo (usado na busca): " . htmlspecialchars($cnpj_limpo) . "</pre>";


$sql = "SELECT id, nome, senha, cargo FROM usuarios WHERE cnpj = $1 AND cargo = 1";

$stmt = pg_prepare($link, "admin_login_debug_query", $sql);

if ($stmt) {
    echo "<pre>Consulta SQL preparada com sucesso.</pre>";
    $result = pg_execute($link, "admin_login_debug_query", array($cnpj_limpo));

    if ($result && pg_num_rows($result) === 1) {
        $usuario = pg_fetch_assoc($result);
        echo "<pre>Usuário encontrado no banco:</pre>";
        var_dump($usuario);

        $hash_do_banco = trim($usuario['senha']);
        echo "<pre>Hash extraído do banco (após trim): " . htmlspecialchars($hash_do_banco) . "</pre>";
        
        echo "<pre>-- VERIFICANDO A SENHA --</pre>";
        $senha_correta = password_verify($senha, $hash_do_banco);
        
        echo "<pre>Resultado do password_verify(): ";
        var_dump($senha_correta);
        echo "</pre>";

    } else {
        echo "<pre>-- ERRO: Usuário não encontrado no banco ou a busca falhou. --</pre>";
        echo "<pre>Linhas encontradas: " . pg_num_rows($result) . "</pre>";
    }
} else {
    echo "<pre>-- ERRO FATAL: pg_prepare() falhou. Verifique a sintaxe SQL ou a conexão. --</pre>";
}

// PARAMOS O SCRIPT AQUI PARA VER A SAÍDA
echo "<pre>-- FIM DO DEBUG --</pre>";
exit;
?>