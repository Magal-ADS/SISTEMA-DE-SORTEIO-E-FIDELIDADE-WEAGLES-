<?php
// /php/verificar_senha_vendedor.php

session_start();
require_once "db_config.php";
header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Ocorreu um erro.'];

$vendedor_id = $_POST['vendedor_id'] ?? 0;
$senha_vendedor = $_POST['senha_vendedor'] ?? '';

if (empty($vendedor_id) || empty($senha_vendedor)) {
    $response['message'] = 'Selecione o vendedor e digite a senha.';
    echo json_encode($response);
    exit;
}

// Busca a senha do vendedor no banco
$stmt = $link->prepare("SELECT senha FROM usuarios WHERE id = ? AND CARGO = 2");
$stmt->bind_param("i", $vendedor_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $vendedor = $result->fetch_assoc();
    // Verifica se a senha fornecida bate com o hash salvo no banco
    if (password_verify($senha_vendedor, $vendedor['senha'])) {
        // SUCESSO! A senha está correta.
        
        // Criamos um "passe livre" na sessão para a próxima página
        // Isso garante que só quem passou por aqui pode acessar a tela de compra
        $_SESSION['vendedor_autenticado_id'] = $vendedor_id;
        
        $response['status'] = 'success';
        unset($response['message']);

    } else {
        // Senha incorreta
        $response['message'] = 'Senha do vendedor incorreta.';
    }
} else {
    $response['message'] = 'Vendedor não encontrado.';
}

$stmt->close();
$link->close();

echo json_encode($response);
?>