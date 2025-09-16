<?php
// /php/processa_login.php (VERSÃO FINAL E CORRIGIDA)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "db_config.php";

$cnpj = trim($_POST['cnpj'] ?? '');
$senha = trim($_POST['senha'] ?? '');

if (empty($cnpj) || empty($senha)) {
    $_SESSION['login_error'] = "CNPJ e senha são obrigatórios.";
    header("Location: ../login.php");
    exit;
}

// CORREÇÃO 1: Nome da coluna 'CARGO' para 'cargo' (minúsculo) para ser compatível com o PostgreSQL.
$sql = "SELECT id, nome, senha, cargo FROM usuarios WHERE cnpj = $1 AND cargo = 1";

// Usando um nome de statement único para evitar conflitos
$stmt = pg_prepare($link, "login_admin_query", $sql);

if ($stmt) {
    $result = pg_execute($link, "login_admin_query", array($cnpj));

    if ($result && pg_num_rows($result) === 1) {
        $usuario = pg_fetch_assoc($result);
        
        if (password_verify($senha, $usuario['senha'])) {
            unset($_SESSION['login_error']);
            
            // CORREÇÃO 2: A linha abaixo foi desativada para resolver o problema de perda de sessão no redirect.
            // session_regenerate_id(true);
            
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            
            // CORREÇÃO 3: Padronizando a variável de sessão para 'cargo', conforme usado no dashboard.php.
            $_SESSION['cargo'] = $usuario['cargo'];
            
            header("Location: ../dashboard.php"); 
            exit;
        }
    }
    
    // Se a consulta falhou, ou não encontrou usuário, ou a senha estava errada
    $_SESSION['login_error'] = "CNPJ ou senha inválidos.";
    header("Location: ../login.php");
    exit;

} else {
    // Se o pg_prepare falhar
    $_SESSION['login_error'] = "Ocorreu um erro no sistema. Tente novamente.";
    header("Location: ../login.php");
    exit;
}

?>