<?php
// /php/editar_funcionario.php

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

// O resto da sua lógica de validação, 100% intacta
$id = $_POST['id'] ?? 0;
$nome = trim($_POST['nome'] ?? '');
$cpf_input = trim($_POST['cpf'] ?? '');
$cargo = $_POST['cargo'] ?? 0;
$senha = trim($_POST['senha'] ?? '');
if (empty($id) || empty($nome) || empty($cpf_input) || empty($cargo)) {
    $response['message'] = 'Todos os campos, exceto a senha, são obrigatórios.';
    echo json_encode($response);
    exit;
}
$cpf_limpo = preg_replace('/[^0-9]/', '', $cpf_input);

// ALTERAÇÃO 1: Lógica para montar a query e os parâmetros dinamicamente para o Postgres
$params = [];
$set_parts = [];
$param_index = 1;

// Adiciona os campos base
$set_parts[] = "nome = $" . $param_index++;
$params[] = $nome;
$set_parts[] = "cpf = $" . $param_index++;
$params[] = $cpf_limpo;
$set_parts[] = "CARGO = $" . $param_index++;
$params[] = $cargo;

// Adiciona a senha apenas se ela foi enviada
if (!empty($senha)) {
    if (strlen($senha) < 6) {
        $response['message'] = 'A nova senha deve ter no mínimo 6 caracteres.';
        echo json_encode($response);
        exit;
    }
    $hash_senha = password_hash($senha, PASSWORD_DEFAULT);
    $set_parts[] = "senha = $" . $param_index++;
    $params[] = $hash_senha;
}

// Junta as partes do SET e adiciona o WHERE
$sql = "UPDATE usuarios SET " . implode(", ", $set_parts) . " WHERE id = $" . $param_index;
$params[] = $id;

// ALTERAÇÃO 2: Bloco de consulta para usar as funções do Postgres
$stmt = pg_prepare($link, "update_funcionario_query", $sql);
if ($stmt) {
    $result = pg_execute($link, "update_funcionario_query", $params);

    // Para um UPDATE, verificamos se a execução foi bem-sucedida
    if ($result) {
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
        // ALTERAÇÃO 3: Tratamento de erro para CPF duplicado no Postgres
        if (pg_result_error_field($result, PGSQL_DIAG_SQLSTATE) == "23505") {
            $response['message'] = 'Este CPF já pertence a outro usuário.';
        } else {
            $response['message'] = 'Erro ao atualizar no banco de dados.';
        }
    }
} else {
    $response['message'] = 'Erro na preparação da consulta de atualização.';
}

pg_close($link);
echo json_encode($response);
?>