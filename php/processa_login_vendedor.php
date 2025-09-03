<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once "db_config.php";

$cpf = trim($_POST['cpf'] ?? '');
$senha = trim($_POST['senha'] ?? '');

if (empty($cpf) || empty($senha)) {
    $_SESSION['login_error'] = "CPF e senha são obrigatórios.";
    header("Location: ../login_vendedora.php");
    exit;
}

// Busca um usuário com o CPF informado E que tenha o CARGO = 2 (Vendedor)
$sql = "SELECT id, nome, senha, CARGO FROM usuarios WHERE cpf = ? AND CARGO = 2";

if ($stmt = $link->prepare($sql)) {
    $stmt->bind_param("s", $cpf);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $usuario = $result->fetch_assoc();
        if (password_verify($senha, $usuario['senha'])) {
            session_regenerate_id(true);
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['cargo'] = $usuario['CARGO'];
            
            // ===== CORREÇÃO AQUI =====
            // Ajustado o nome do arquivo para o feminino "vendedora"
            header("Location: ../dashboard_vendedora.php"); 
            exit;
        }
    }
    
    // Se não encontrou ou a senha estava errada
    $_SESSION['login_error'] = "CPF ou senha inválidos.";
    header("Location: ../login_vendedora.php");
    exit;
}
?>