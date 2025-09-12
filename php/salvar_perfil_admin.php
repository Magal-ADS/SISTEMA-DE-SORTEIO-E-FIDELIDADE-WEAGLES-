<?php
// /php/salvar_perfil_admin.php

session_start();
require_once "db_config.php";
header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Acesso não autorizado.'];

// Segurança: Apenas um admin logado pode editar o próprio perfil
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

// 1. VERIFICA A SENHA ATUAL DO ADMIN
$stmt_senha = $link->prepare("SELECT senha FROM usuarios WHERE id = ?");
$stmt_senha->bind_param("i", $admin_id);
$stmt_senha->execute();
$result_senha = $stmt_senha->get_result();
$admin_data = $result_senha->fetch_assoc();
$stmt_senha->close();

if (!$admin_data || !password_verify($senha_atual, $admin_data['senha'])) {
    $response['message'] = 'A sua senha atual está incorreta.';
    echo json_encode($response);
    exit;
}

// 2. SE A SENHA ATUAL ESTIVER CORRETA, PROSSEGUE COM A ATUALIZAÇÃO
// Lógica para atualizar a senha apenas se uma nova foi fornecida
if (!empty($nova_senha)) {
    $hash_nova_senha = password_hash($nova_senha, PASSWORD_DEFAULT);
    $sql = "UPDATE usuarios SET nome = ?, cnpj = ?, senha = ? WHERE id = ?";
    $stmt = $link->prepare($sql);
    $stmt->bind_param("sssi", $nome, $cnpj, $hash_nova_senha, $admin_id);
} else {
    $sql = "UPDATE usuarios SET nome = ?, cnpj = ? WHERE id = ?";
    $stmt = $link->prepare($sql);
    $stmt->bind_param("ssi", $nome, $cnpj, $admin_id);
}

if ($stmt->execute()) {
    // Atualiza o nome na sessão para refletir a mudança imediatamente no header
    $_SESSION['usuario_nome'] = $nome;
    $response['status'] = 'success';
    $response['message'] = 'Perfil atualizado com sucesso!';
} else {
    $response['message'] = 'Erro ao atualizar. O CNPJ pode já estar em uso.';
}

$stmt->close();
$link->close();
echo json_encode($response);
?>