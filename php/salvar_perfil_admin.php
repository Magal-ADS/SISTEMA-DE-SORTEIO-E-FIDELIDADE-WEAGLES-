<?php
// /php/salvar_perfil_admin.php (Versão PostgreSQL)

session_start();
require_once "db_config.php";
header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Acesso não autorizado.'];

// Segurança (inalterada)
if (!isset($_SESSION['cargo']) || $_SESSION['cargo'] != 1) {
    echo json_encode($response);
    exit;
}

$admin_id = $_SESSION['usuario_id'];
$nome = $_POST['nome'] ?? '';
$cnpj = $_POST['cnpj'] ?? '';
$nova_senha = $_POST['senha'] ?? '';
$senha_atual = $_POST['senha_atual'] ?? '';

if (empty($nome) || empty($cnpj) || empty($senha_atual)) {
    $response['message'] = 'Nome, CNPJ e a senha atual são obrigatórios.';
    echo json_encode($response);
    exit;
}

// --- ETAPA 1: Verificar a senha atual do admin ---
$sql_senha = "SELECT senha FROM usuarios WHERE id = $1";
$stmt_senha = pg_prepare($link, "check_current_pass_query", $sql_senha);

if (!$stmt_senha) {
    $response['message'] = 'Erro crítico ao preparar a verificação de segurança.';
    echo json_encode($response);
    exit;
}

$result_senha = pg_execute($link, "check_current_pass_query", [$admin_id]);
$admin_data = pg_fetch_assoc($result_senha);

if (!$admin_data || !password_verify($senha_atual, $admin_data['senha'])) {
    $response['message'] = 'A sua senha atual está incorreta.';
    echo json_encode($response);
    exit;
}

// --- ETAPA 2: Preparar e executar a atualização do perfil ---
$params = [];
$sql_update = '';
$query_name = '';

// A lógica condicional para atualizar a senha (ou não) é mantida
if (!empty($nova_senha)) {
    // BÔNUS: Validação mínima da nova senha
    if (strlen($nova_senha) < 6) {
        $response['message'] = 'A nova senha deve ter no mínimo 6 caracteres.';
        echo json_encode($response);
        exit;
    }
    $hash_nova_senha = password_hash($nova_senha, PASSWORD_DEFAULT);
    $sql_update = "UPDATE usuarios SET nome = $1, cnpj = $2, senha = $3 WHERE id = $4";
    $query_name = "update_admin_com_senha";
    $params = [$nome, $cnpj, $hash_nova_senha, $admin_id];
} else {
    $sql_update = "UPDATE usuarios SET nome = $1, cnpj = $2 WHERE id = $3";
    $query_name = "update_admin_sem_senha";
    $params = [$nome, $cnpj, $admin_id];
}

$stmt_update = pg_prepare($link, $query_name, $sql_update);

if ($stmt_update) {
    $result_update = pg_execute($link, $query_name, $params);

    if ($result_update) {
        // Atualiza o nome na sessão para refletir a mudança imediatamente
        $_SESSION['usuario_nome'] = $nome;
        $response['status'] = 'success';
        $response['message'] = 'Perfil atualizado com sucesso!';
    } else {
        // A falha provavelmente é por causa da restrição UNIQUE no CNPJ
        $response['message'] = 'Erro ao atualizar. O CNPJ informado pode já estar em uso por outro usuário.';
    }
} else {
    $response['message'] = 'Erro na preparação da consulta de atualização do perfil.';
}

pg_close($link);
echo json_encode($response);
?>