<?php
// /php/remover_funcionario.php (VERSÃO FINAL COM LÓGICA DE VERIFICAÇÃO ROBUSTA)

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
$stmt_admin = pg_prepare($link, "remover_func_get_admin_pass", $sql_admin);
if (!$stmt_admin) {
    $response['message'] = 'Erro (R1) na preparação da consulta.';
    echo json_encode($response);
    exit;
}
$result_admin = pg_execute($link, "remover_func_get_admin_pass", [$admin_id_logado]);
$admin_data = pg_fetch_assoc($result_admin);

if (!$admin_data || !password_verify($senha_admin_digitada, $admin_data['senha'])) {
    $response['message'] = 'Senha do administrador incorreta.';
    echo json_encode($response);
    exit;
}

// ETAPA 2: Verificar se o funcionário possui vendas associadas
$sql_check_vendas = "SELECT COUNT(id) as total_vendas FROM compras WHERE vendedor_id = $1";
$stmt_check_vendas = pg_prepare($link, "remover_func_check_vendas", $sql_check_vendas);
if (!$stmt_check_vendas) {
    $response['message'] = 'Erro (R2) na preparação da consulta.';
    echo json_encode($response);
    exit;
}
$result_check = pg_execute($link, "remover_func_check_vendas", [$funcionario_id_para_remover]);
$vendas = pg_fetch_assoc($result_check);

// =================== LÓGICA DE DECISÃO CORRIGIDA ===================
// Agora, só tentamos deletar se a contagem de vendas for exatamente 0.

if ($vendas && $vendas['total_vendas'] > 0) {
    // Caso 1: Funcionário tem vendas, não pode ser removido.
    $response['message'] = 'Este funcionário não pode ser removido pois possui ' . $vendas['total_vendas'] . ' venda(s) associada(s) a ele.';

} else if ($vendas && $vendas['total_vendas'] == 0) {
    // Caso 2: Funcionário não tem vendas, pode ser removido.
    $sql_delete = "DELETE FROM usuarios WHERE id = $1";
    $stmt_delete = pg_prepare($link, "remover_func_delete_final", $sql_delete);
    if ($stmt_delete) {
        $result_delete = pg_execute($link, "remover_func_delete_final", [$funcionario_id_para_remover]);
        if ($result_delete && pg_affected_rows($result_delete) > 0) {
            $response = ['status' => 'success', 'message' => 'Funcionário removido com sucesso!'];
        } else {
            $response['message'] = 'Erro (R3) ao remover funcionário. Ele pode já ter sido removido.';
        }
    } else {
        $response['message'] = 'Erro (R4) na preparação da consulta de remoção.';
    }
} else {
    // Caso 3: Falha na verificação de vendas.
    $response['message'] = 'Não foi possível verificar as vendas do funcionário.';
}
// ====================================================================

pg_close($link);
echo json_encode($response);
?>