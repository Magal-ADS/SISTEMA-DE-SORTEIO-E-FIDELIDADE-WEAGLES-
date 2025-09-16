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

// ALTERAÇÃO 1: O placeholder '?' foi trocado por '$1' para o Postgres
$sql = "SELECT id, nome, senha, CARGO FROM usuarios WHERE cnpj = $1 AND CARGO = 1"; // Garantir que é um Admin

// ALTERAÇÃO 2: Todo o bloco de mysqli foi trocado pelo de pg_
// Prepara a consulta e dá um nome a ela ("login_query")
$stmt = pg_prepare($link, "login_query", $sql);

if ($stmt) {
    // Executa a consulta preparada, passando os parâmetros em um array
    $result = pg_execute($link, "login_query", array($cnpj));

    // pg_num_rows para contar as linhas
    if ($result && pg_num_rows($result) === 1) {
        // pg_fetch_assoc para pegar os dados do usuário
        $usuario = pg_fetch_assoc($result);
        
        // O resto da sua lógica continua igual, pois é PHP puro
        if (password_verify($senha, $usuario['senha'])) {
            unset($_SESSION['login_error']);
            session_regenerate_id(true);
            
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            
            // CORREÇÃO do seu código original: A session deve ser 'usuario_cargo' para ser compatível com o header.php
            $_SESSION['usuario_cargo'] = $usuario['cargo']; // Note que o nome da coluna no Postgres ficou minúsculo
            
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