<?php
// /php/editar_funcionario.php

session_start();
require_once "db_config.php";
header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Ocorreu um erro.'];

// Segurança: Apenas um admin logado pode editar usuários.
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['usuario_cargo']) || $_SESSION['usuario_cargo'] != 1) {
    $response['message'] = 'Acesso não autorizado.';
    echo json_encode($response);
    exit;
}

// 1. Recebe os dados do formulário de edição
$id = $_POST['id'] ?? 0;
$nome = trim($_POST['nome'] ?? '');
$cpf_input = trim($_POST['cpf'] ?? '');
$cargo = $_POST['cargo'] ?? 0;
$senha = trim($_POST['senha'] ?? ''); // Senha é opcional

if (empty($id) || empty($nome) || empty($cpf_input) || empty($cargo)) {
    $response['message'] = 'Todos os campos, exceto a senha, são obrigatórios.';
    echo json_encode($response);
    exit;
}

// 2. Trata os dados
$cpf_limpo = preg_replace('/[^0-9]/', '', $cpf_input);
$sql_parts = [];
$params = [];
$types = '';

// Prepara a query dinamicamente
$sql_base = "UPDATE usuarios SET nome = ?, cpf = ?, CARGO = ? ";
$types .= 'ssi';
array_push($params, $nome, $cpf_limpo, $cargo);

// Se uma nova senha foi informada, adiciona à query
if (!empty($senha)) {
    if (strlen($senha) < 6) {
        $response['message'] = 'A nova senha deve ter no mínimo 6 caracteres.';
        echo json_encode($response);
        exit;
    }
    $hash_senha = password_hash($senha, PASSWORD_DEFAULT);
    $sql_base .= ", senha = ? ";
    $types .= 's';
    array_push($params, $hash_senha);
}

$sql_base .= "WHERE id = ?";
$types .= 'i';
array_push($params, $id);

// 3. Executa o UPDATE no banco
if ($stmt = $link->prepare($sql_base)) {
    $stmt->bind_param($types, ...$params);
    
    if ($stmt->execute()) {
        $response = [
            'status' => 'success',
            'message' => 'Funcionário atualizado com sucesso!',
            'funcionarioAtualizado' => [
                'id' => $id,
                'nome' => $nome,
                'cpf' => $cpf_input,
                'cargo' => ($cargo == 2) ? 'Vendedor' : 'Administrador'
            ]
        ];
    } else {
        if ($link->errno == 1062) {
            $response['message'] = 'Este CPF já pertence a outro usuário.';
        } else {
            $response['message'] = 'Erro ao atualizar no banco de dados.';
        }
    }
    $stmt->close();
} else {
    $response['message'] = 'Erro na preparação da consulta de atualização.';
}

$link->close();
echo json_encode($response);
?>