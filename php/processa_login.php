<?php
// /php/processa_login.php

// Garante que a sessão seja iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "db_config.php";

$cnpj = trim($_POST['cnpj'] ?? '');
$senha = trim($_POST['senha'] ?? '');

if (empty($cnpj) || empty($senha)) {
    $_SESSION['login_error'] = "CNPJ e senha são obrigatórios.";
    // Redireciona de volta para a PÁGINA DE LOGIN
    header("Location: ../login.php");
    exit;
}

// 1. CORREÇÃO: Alterado "nome_empresa" para "nome" para corresponder ao banco de dados.
$sql = "SELECT id, nome, senha, CARGO FROM usuarios WHERE cnpj = ? AND CARGO = 1"; // Garantir que é um Admin

if ($stmt = $link->prepare($sql)) {
    $stmt->bind_param("s", $cnpj);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $usuario = $result->fetch_assoc();
        
        // Verifica a senha hash
        if (password_verify($senha, $usuario['senha'])) {
            // Limpa qualquer erro de login anterior
            unset($_SESSION['login_error']);
            
            // Armazena os dados do usuário na sessão
            $_SESSION['usuario_id'] = $usuario['id'];
            // 2. CORREÇÃO: Usar a chave correta 'nome'
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['usuario_cargo'] = $usuario['CARGO'];
            
            // Força o salvamento da sessão antes do redirecionamento
            session_write_close(); 
            
            // 3. CORREÇÃO: Redireciona para o DASHBOARD do admin após o login!
            header("Location: ../dashboard.php"); 
            exit;
        }
    }
    
    // Se a consulta falhou, o número de linhas não foi 1 ou a senha estava errada
    $_SESSION['login_error'] = "CNPJ ou senha inválidos.";
    // Redireciona de volta para a PÁGINA DE LOGIN
    header("Location: ../login.php");
    exit;
} else {
    // Erro na preparação da query
    $_SESSION['login_error'] = "Ocorreu um erro no sistema. Tente novamente.";
    header("Location: ../login.php");
    exit;
}
?>