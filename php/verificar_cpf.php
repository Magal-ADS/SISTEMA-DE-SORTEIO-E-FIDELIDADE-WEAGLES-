<?php
// /php/verificar_cpf.php

// Inicia a sessão para que possamos salvar os dados do cliente para a próxima página
session_start();

// Define que a resposta será um JSON
header('Content-Type: application/json');

// Inclui o arquivo de conexão. A variável de conexão $link virá daqui.
require_once "db_config.php";

// Prepara uma resposta padrão
$response = ['status' => 'error', 'message' => 'Ocorreu um erro desconhecido.'];

// Pega o CPF enviado via POST
$cpf = $_POST['cpf'] ?? '';

if (empty($cpf)) {
    $response['message'] = 'CPF não fornecido.';
    echo json_encode($response);
    exit;
}

// Prepara a consulta SQL de forma segura usando a variável $link
$sql = "SELECT id, nome_completo FROM clientes WHERE cpf = ?";

// Use a variável '$link' que veio do db_config.php
if ($stmt = $link->prepare($sql)) {
    $stmt->bind_param("s", $cpf);
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Cliente EXISTE
            $cliente = $result->fetch_assoc();
            
            // Salva os dados na sessão para a próxima página
            $_SESSION['cliente_id'] = $cliente['id'];
            $_SESSION['cliente_nome'] = $cliente['nome_completo'];
            $_SESSION['cpf_cliente'] = $cpf;
            
            // Prepara a resposta de sucesso
            $response = ['status' => 'exists', 'redirect' => 'confirmacao_cliente.php'];
        } else {
            // Cliente NÃO EXISTE
            $_SESSION['cpf_digitado'] = $cpf;
            
            $response = ['status' => 'not_exists', 'redirect' => 'cadastro.php'];
        }
    } else {
        $response['message'] = "Erro ao executar a consulta: " . $stmt->error;
    }
    $stmt->close();
} else {
    // Use a variável '$link' aqui também para a mensagem de erro
    $response['message'] = "Erro na preparação da consulta: " . $link->error;
}

// Fecha a conexão usando a variável $link
$link->close();

// Envia a resposta final em formato JSON
echo json_encode($response);
?>