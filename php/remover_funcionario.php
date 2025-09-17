<?php
// /php/remover_funcionario.php (VERSÃO COM SOFT DELETE)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "db_config.php";
header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Ocorreu um erro.'];

if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['cargo']) || $_SESSION['cargo'] != 1) {
    $response['message'] = 'Acesso não autorizado.';
    echo json_encode($response);
    exit;
}

$admin_id_logado = $_SESSION['usuario_id'];
$funcionario_id_para_remover = $_POST['id'] ?? 0;
$senha_admin_digitada = $_POST['senha_admin'] ?? '';

if (empty($funcionario_id_para_remover) || empty($senha_admin_digitada)) {
    $response['message'] = 'ID do funcionário e senha do admin são obrigatórios.';
    echo json_encode($response);
    exit;
}

// ETAPA 1: Verificar a senha do administrador logado
$sql_admin = "SELECT senha FROM usuarios WHERE id = $1";
$stmt_admin = pg_prepare($link, "soft_delete_get_admin_pass", $sql_admin);
if (!$stmt_admin) {
    $response['message'] = 'Erro (R1) na preparação da consulta.';
    echo json_encode($response);
    exit;
}
$result_admin = pg_execute($link, "soft_delete_get_admin_pass", [$admin_id_logado]);
$admin_data = pg_fetch_assoc($result_admin);

if (!$admin_data || !password_verify($senha_admin_digitada, $admin_data['senha'])) {
    $response['message'] = 'Senha do administrador incorreta.';
    echo json_encode($response);
    exit;
}

// ETAPA 2: Se a senha estiver correta, INATIVA o funcionário (UPDATE, não DELETE)
$sql_inativar = "UPDATE usuarios SET ativo = FALSE WHERE id = $1";
$stmt_inativar = pg_prepare($link, "inativar_funcionario_query", $sql_inativar);

if ($stmt_inativar) {
    $result_inativar = pg_execute($link, "inativar_funcionario_query", [$funcionario_id_para_remover]);
    if ($result_inativar && pg_affected_rows($result_inativar) > 0) {
        $response = ['status' => 'success', 'message' => 'Funcionário inativado com sucesso!'];
    } else {
        $response['message'] = 'Nenhum funcionário encontrado com este ID ou erro ao inativar.';
    }
} else {
    $response['message'] = 'Erro na preparação da consulta de inativação.';
}

pg_close($link);
echo json_encode($response);
?>