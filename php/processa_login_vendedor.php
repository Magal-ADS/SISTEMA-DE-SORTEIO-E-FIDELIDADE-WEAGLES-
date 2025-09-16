<?php
// /php/processa_login_vendedor.php (VERSÃO FINAL E CORRIGIDA)

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once "db_config.php";

$cpf = trim($_POST['cpf'] ?? '');
$senha = trim($_POST['senha'] ?? '');

if (empty($cpf) || empty($senha)) {
    $_SESSION['login_error'] = "CPF e senha são obrigatórios.";
    header("Location: ../login_vendedora.php");
    exit;
}

// =================== CORREÇÃO APLICADA AQUI ===================
// Adicionamos esta linha para remover os pontos e o traço do CPF
$cpf_limpo = preg_replace('/[^0-9]/', '', $cpf);
// =============================================================

$sql = "SELECT id, nome, senha, cargo FROM usuarios WHERE cpf = $1 AND cargo = 2";

$stmt = pg_prepare($link, "vendedor_login_query", $sql);

if ($stmt) {
    // Agora usamos o $cpf_limpo na busca
    $result = pg_execute($link, "vendedor_login_query", array($cpf_limpo));

    if ($result && pg_num_rows($result) === 1) {
        $usuario = pg_fetch_assoc($result);
        if (password_verify($senha, $usuario['senha'])) {
            // session_regenerate_id(true); // Desativado para Heroku
            
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['cargo'] = $usuario['cargo']; 
            
            header("Location: ../dashboard_vendedora.php"); 
            exit;
        }
    }
    
    // Se não encontrou ou a senha estava errada
    $_SESSION['login_error'] = "CPF ou senha inválidos.";
    header("Location: ../login_vendedora.php");
    exit;
} else {
    // Se o pg_prepare falhar
    $_SESSION['login_error'] = "Ocorreu um erro no sistema. Tente novamente.";
    header("Location: ../login_vendedora.php");
    exit;
}
?>