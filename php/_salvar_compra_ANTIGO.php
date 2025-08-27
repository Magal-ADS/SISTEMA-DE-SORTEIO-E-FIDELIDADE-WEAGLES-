<?php
// /php/salvar_compra.php

session_start();
require_once "db_config.php";
header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Ocorreu um erro desconhecido.'];

$cliente_id = $_POST['cliente_id'] ?? 0;
$vendedor_id = $_POST['vendedor_id'] ?? 0;
$valor_formatado = $_POST['valor'] ?? '0';

if (empty($cliente_id) || empty($vendedor_id) || empty($valor_formatado)) {
    $response['message'] = 'Todos os campos são obrigatórios.';
    echo json_encode($response);
    exit;
}

$valor_sem_ponto = str_replace('.', '', $valor_formatado);
$valor_para_banco = str_replace(',', '.', $valor_sem_ponto);

$usuario_id = $_SESSION['usuario_id'] ?? 1;

$sql_compra = "INSERT INTO compras (cliente_id, valor, vendedor_id, usuario_id) VALUES (?, ?, ?, ?)";

if ($stmt_compra = $link->prepare($sql_compra)) {
    $stmt_compra->bind_param("idii", $cliente_id, $valor_para_banco, $vendedor_id, $usuario_id);
    
    if ($stmt_compra->execute()) {
        $response = [
            'status' => 'success', 
            'message' => 'Compra registrada com sucesso!',
            // LINHA CORRIGIDA ABAIXO
            'redirect' => 'sucesso.php'
        ];
    } else {
        $response['message'] = 'Erro ao salvar a compra no banco de dados.';
    }
    $stmt_compra->close();
} else {
    $response['message'] = 'Erro na preparação da consulta SQL.';
}

$link->close();
echo json_encode($response);
?>