<?php
// /php/verificar_senha_geral.php
session_start();
header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Ocorreu um erro.'];
$senha_digitada = $_POST['senha_geral'] ?? '';

// A senha geral e fixa
$senha_correta = '1';

if (empty($senha_digitada)) {
    $response['message'] = 'Por favor, digite a senha.';
} else if ($senha_digitada === $senha_correta) {
    // Deu certo! Cria a "pulseira VIP" correta na sessão.
    $_SESSION['vendedor_autenticado'] = true;
    $response['status'] = 'success';
    unset($response['message']);
} else {
    $response['message'] = 'Senha de liberação incorreta.';
}

echo json_encode($response);
?>