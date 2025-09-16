<?php
// /php/editar_funcionario.php (VERSÃO FINAL E CORRIGIDA)

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

// Validação dos dados (sem alterações)
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

// Lógica para montar a query e os parâmetros dinamicamente
$params = [];
$set_parts = [];
$param_index = 1;

$set_parts[] = "nome = $" . $param_index++;
$params[] = $nome;
$set_parts[] = "cpf = $" . $param_index++;
$params[] = $cpf_limpo;

// CORREÇÃO 2: Nome da coluna 'CARGO' para 'cargo' (minúsculo)
$set_parts[] = "cargo = $" . $param_index++;
$params[] = $cargo;

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

$sql = "UPDATE usuarios SET " . implode(", ", $set_parts) . " WHERE id = $" . $param_index;
$params[] = $id;

// Bloco de consulta para usar as funções do Postgres
$stmt = pg_prepare($link, "update_funcionario_query", $sql);
if ($stmt) {
    $result = pg_execute($link, "update_funcionario_query", $params);

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
        // Tratamento de erro para CPF duplicado no Postgres
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