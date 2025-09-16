<?php
// /php/get_funcionario.php (VERSÃO FINAL E CORRIGIDA)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "db_config.php";
header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Ocorreu um erro.'];

// CORREÇÃO 1: Padronizando a verificação da variável de sessão para 'cargo'
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['cargo']) || $_SESSION['cargo'] != 1) {
    $response['message'] = 'Acesso não autorizado.';
    echo json_encode($response);
    exit;
}

$id = $_GET['id'] ?? 0;
if (empty($id)) {
    $response['message'] = 'ID do funcionário não fornecido.';
    echo json_encode($response);
    exit;
}

// CORREÇÃO 2: Nome da coluna 'CARGO' para 'cargo' (minúsculo) na consulta SQL
$sql = "SELECT nome, cpf, cargo FROM usuarios WHERE id = $1";

$stmt = pg_prepare($link, "get_funcionario_query", $sql);
if ($stmt) {
    $result = pg_execute($link, "get_funcionario_query", array($id));

    if ($result && pg_num_rows($result) > 0) {
        $funcionario = pg_fetch_assoc($result);
        
        // O array $funcionario já vem no formato correto, então podemos usá-lo diretamente
        $response = [
            'status' => 'success',
            'funcionario' => $funcionario
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