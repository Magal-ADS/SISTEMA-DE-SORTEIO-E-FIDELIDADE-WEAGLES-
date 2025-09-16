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

// ALTERADO: Placeholder '?' para '$1'
// Busca um usuário com o CPF informado E que tenha o CARGO = 2 (Vendedor)
$sql = "SELECT id, nome, senha, CARGO FROM usuarios WHERE cpf = $1 AND CARGO = 2";

// ALTERADO: Bloco de consulta para usar as funções do Postgres
$stmt = pg_prepare($link, "vendedor_login_query", $sql);

if ($stmt) {
    $result = pg_execute($link, "vendedor_login_query", array($cpf));

    if ($result && pg_num_rows($result) === 1) {
        $usuario = pg_fetch_assoc($result);
        if (password_verify($senha, $usuario['senha'])) {
            session_regenerate_id(true);
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            // Ajuste para minúsculas, pois o Postgres costuma retornar assim
            $_SESSION['usuario_cargo'] = $usuario['cargo']; 
            
            // Redireciona para o dashboard da vendedora
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