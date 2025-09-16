<?php
// /php/adicionar_funcionario.php

session_start();
require_once "db_config.php";
header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Ocorreu um erro desconhecido.'];

// BLOCO DE SEGURANÇA CORRIGIDO
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['usuario_cargo']) || $_SESSION['usuario_cargo'] != 1) {
    $response['message'] = 'Acesso não autorizado.';
    echo json_encode($response);
    exit;
}

// O resto da sua lógica de validação, 100% intacta
$nome = trim($_POST['nome'] ?? '');
$cpf_input = trim($_POST['cpf'] ?? '');
$senha = trim($_POST['senha'] ?? '');
$cargo = $_POST['cargo'] ?? 0;

if (empty($nome) || empty($cpf_input) || empty($senha) || empty($cargo)) {
    $response['message'] = 'Todos os campos são obrigatórios.';
    echo json_encode($response);
    exit;
}
if (strlen($senha) < 6) {
    $response['message'] = 'A senha deve ter no mínimo 6 caracteres.';
    echo json_encode($response);
    exit;
}
$cpf_limpo = preg_replace('/[^0-9]/', '', $cpf_input);
$hash_senha = password_hash($senha, PASSWORD_DEFAULT);

// ALTERAÇÃO 1: SQL com placeholders do Postgres e 'RETURNING id' para pegar o novo ID
$sql = "INSERT INTO usuarios (nome, cpf, senha, CARGO) VALUES ($1, $2, $3, $4) RETURNING id";

// ALTERAÇÃO 2: Bloco de consulta para usar as funções do Postgres
$stmt = pg_prepare($link, "add_funcionario_query", $sql);
if ($stmt) {
    $result = pg_execute($link, "add_funcionario_query", array($nome, $cpf_limpo, $hash_senha, $cargo));

    if ($result && pg_num_rows($result) > 0) {
        // Pega o ID que o "RETURNING id" devolveu
        $row = pg_fetch_assoc($result);
        $novo_id = $row['id'];
        
        $response = [
            'status' => 'success',
            'message' => 'Funcionário adicionado com sucesso!',
            'novoFuncionario' => [
                'id' => $novo_id,
                'nome' => $nome,
                'cpf' => $cpf_input,
                'cargo' => ($cargo == 2) ? 'Vendedor' : 'Administrador'
            ]
        ];
    } else {
        // ALTERAÇÃO 3: Tratamento de erro para chave duplicada no Postgres
        // O código de erro padrão para "chave única duplicada" no Postgres é '23505'
        if (pg_result_error_field($result, PGSQL_DIAG_SQLSTATE) == "23505") {
            $response['message'] = 'Este CPF já está cadastrado no sistema.';
        } else {
            $response['message'] = 'Erro ao salvar no banco de dados.';
        }
    }
} else {
    $response['message'] = 'Erro na preparação da consulta.';
}

pg_close($link);
echo json_encode($response);
?>