<?php
// /php/remover_funcionario.php (Versão com verificação de senha do admin)

session_start();
require_once "db_config.php";
header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Ocorreu um erro.'];

// Segurança: Apenas um admin logado pode remover usuários.
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['usuario_cargo']) || $_SESSION['usuario_cargo'] != 1) {
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

// 1. VERIFICA A SENHA DO ADMIN LOGADO
$stmt_admin = $link->prepare("SELECT senha FROM usuarios WHERE id = ?");
$stmt_admin->bind_param("i", $admin_id_logado);
$stmt_admin->execute();
$result_admin = $stmt_admin->get_result();
$admin_data = $result_admin->fetch_assoc();
$stmt_admin->close();

if (!$admin_data || !password_verify($senha_admin_digitada, $admin_data['senha'])) {
    $response['message'] = 'Senha do administrador incorreta.';
    echo json_encode($response);
    exit;
}

// 2. SE A SENHA ESTIVER CORRETA, PROSSEGUE COM A REMOÇÃO
$sql = "DELETE FROM usuarios WHERE id = ?";
if ($stmt = $link->prepare($sql)) {
    $stmt->bind_param("i", $funcionario_id_para_remover);
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        $response = ['status' => 'success', 'message' => 'Funcionário removido com sucesso!'];
    } else {
        $response['message'] = 'Nenhum funcionário encontrado com este ID ou erro ao remover.';
    }
    $stmt->close();
} else {
     $response['message'] = 'Erro na preparação da consulta de remoção.';
}

$link->close();
echo json_encode($response);
?>