<?php
// /php/get_funcionario.php

session_start();
require_once "db_config.php";
header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Ocorreu um erro.'];

// BLOCO DE SEGURANÇA CORRIGIDO
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['usuario_cargo']) || $_SESSION['usuario_cargo'] != 1) {
    $response['message'] = 'Acesso não autorizado.';
    echo json_encode($response);
    exit;
}

// O resto do seu código, 100% intacto
$id = $_GET['id'] ?? 0;
if (empty($id)) {
    $response['message'] = 'ID do funcionário não fornecido.';
    echo json_encode($response);
    exit;
}

// ALTERADO: SQL com placeholder do Postgres
$sql = "SELECT nome, cpf, CARGO FROM usuarios WHERE id = $1";

// ALTERADO: Bloco de consulta para usar as funções do Postgres
$stmt = pg_prepare($link, "get_funcionario_query", $sql);
if ($stmt) {
    $result = pg_execute($link, "get_funcionario_query", array($id));

    if ($result && pg_num_rows($result) > 0) {
        $funcionario = pg_fetch_assoc($result);
        
        // Ajustando o nome da chave para minúsculas para consistência
        $funcionario_response = [
            'nome' => $funcionario['nome'],
            'cpf' => $funcionario['cpf'],
            'cargo' => $funcionario['cargo'] // Era 'CARGO'
        ];

        $response = [
            'status' => 'success',
            'funcionario' => $funcionario_response
        ];
    } else {
        $response['message'] = 'Funcionário não encontrado.';
    }
} else {
    $response['message'] = 'Erro na preparação da consulta.';
}

pg_close($link);
echo json_encode($response);
?>