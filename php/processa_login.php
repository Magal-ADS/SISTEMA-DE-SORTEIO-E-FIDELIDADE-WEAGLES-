<?php
// /php/processa_login.php

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

$sql = "SELECT id, nome, senha, CARGO FROM usuarios WHERE cnpj = ? AND CARGO = 1"; // Garantir que é um Admin

if ($stmt = $link->prepare($sql)) {
    $stmt->bind_param("s", $cnpj);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $usuario = $result->fetch_assoc();
        
        if (password_verify($senha, $usuario['senha'])) {
            unset($_SESSION['login_error']);
            session_regenerate_id(true);
            
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            
            // AQUI ESTÁ A CORREÇÃO:
            // Mudamos de 'usuario_cargo' para 'cargo' para ser compatível com o header.php
            $_SESSION['cargo'] = $usuario['CARGO'];
            
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