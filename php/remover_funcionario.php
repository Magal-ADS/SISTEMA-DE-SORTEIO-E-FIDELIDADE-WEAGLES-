<?php
// /php/remover_funcionario.php (VERSÃO FINAL E CORRIGIDA)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "db_config.php";
header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Ocorreu um erro.'];

// CORREÇÃO: Padronizando o bloco de segurança para o formato correto.
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['cargo']) || $_SESSION['cargo'] != 1) {
    $response['message'] = 'Acesso não autorizado.';
    echo json_encode($response);
    exit;
}

// Coleta de dados (sem alterações)
$admin_id_logado = $_SESSION['usuario_id'];
$funcionario_id_para_remover = $_POST['id'] ?? 0;
$senha_admin_digitada = $_POST['senha_admin'] ?? '';

if (empty($funcionario_id_para_remover) || empty($senha_admin_digitada)) {
    $response['message'] = 'ID do funcionário e senha do admin são obrigatórios.';
    echo json_encode($response);
    exit;
}

// --- ETAPA 1: Verificar a senha do administrador logado ---
$sql_admin = "SELECT senha FROM usuarios WHERE id = $1";
$stmt_admin = pg_prepare($link, "get_admin_pass_query_for_delete", $sql_admin);

if (!$stmt_admin) {
    $response['message'] = 'Erro crítico ao preparar a verificação de segurança.';
    echo json_encode($response);
    exit;
}

$result_admin = pg_execute($link, "get_admin_pass_query_for_delete", [$admin_id_logado]);
$admin_data = pg_fetch_assoc($result_admin);

if (!$admin_data || !password_verify($senha_admin_digitada, $admin_data['senha'])) {
    $response['message'] = 'Senha do administrador incorreta.';
    echo json_encode($response);
    exit;
}

// --- ETAPA 2: Se a senha estiver correta, remover o funcionário ---
$sql_delete = "DELETE FROM usuarios WHERE id = $1";
$stmt_delete = pg_prepare($link, "remove_funcionario_query", $sql_delete);

if ($stmt_delete) {
    $result_delete = pg_execute($link, "remove_funcionario_query", [$funcionario_id_para_remover]);

    if ($result_delete && pg_affected_rows($result_delete) > 0) {
        $response = ['status' => 'success', 'message' => 'Funcionário removido com sucesso!'];
    } else {
        $response['message'] = 'Nenhum funcionário encontrado com este ID ou erro ao remover.';
    }
} else {
    $response['message'] = 'Erro na preparação da consulta de remoção.';
}

pg_close($link);
echo json_encode($response);
?>  

