<?php
// /php/processa_login.php (VERSÃO COM CORREÇÃO FINAL DE TRIM)

if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}
require_once "db_config.php";

$cnpj_formatado = trim($_POST['cnpj'] ?? '');
$senha = trim($_POST['senha'] ?? '');

if (empty($cnpj_formatado) || empty($senha)) {
    $_SESSION['login_error'] = "CNPJ e senha são obrigatórios.";
    header("Location: ../login.php");
    exit;
}

$cnpj_limpo = preg_replace('/[^0-9]/', '', $cnpj_formatado);

$sql = "SELECT id, nome, senha, cargo FROM usuarios WHERE cnpj = $1 AND cargo = 1";

$stmt = pg_prepare($link, "admin_login_query", $sql);

if ($stmt) {
    $result = pg_execute($link, "admin_login_query", array($cnpj_limpo));

    if ($result && pg_num_rows($result) === 1) {
        $usuario = pg_fetch_assoc($result);
        
        // =================== CORREÇÃO FINAL E DEFINITIVA AQUI ===================
        // Usamos trim() para remover qualquer espaço em branco extra do hash vindo do banco
        if (password_verify($senha, trim($usuario['senha']))) {
        // ========================================================================
            
            unset($_SESSION['login_error']);
            
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['cargo'] = $usuario['cargo'];
            
            header("Location: ../dashboard.php"); 
            exit;
        }
    }
    
    $_SESSION['login_error'] = "CNPJ ou senha inválidos.";
    header("Location: ../login.php");
    exit;

} else {
    $_SESSION['login_error'] = "Ocorreu um erro no sistema. Tente novamente.";
    header("Location: ../login.php");
    exit;
}
?>