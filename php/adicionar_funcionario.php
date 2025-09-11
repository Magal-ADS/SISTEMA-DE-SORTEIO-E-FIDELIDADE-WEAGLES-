<?php
// /php/adicionar_funcionario.php

session_start();
require_once "db_config.php";
header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Ocorreu um erro desconhecido.'];

// BLOCO DE SEGURANÇA CORRIGIDO
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['cargo']) || $_SESSION['cargo'] != 1) {
    $response['message'] = 'Acesso não autorizado.';
    echo json_encode($response);
    exit;
}

// O resto do seu código, 100% intacto
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
$sql = "INSERT INTO usuarios (nome, cpf, senha, CARGO) VALUES (?, ?, ?, ?)";
if ($stmt = $link->prepare($sql)) {
    $stmt->bind_param("sssi", $nome, $cpf_limpo, $hash_senha, $cargo);
    if ($stmt->execute()) {
        $novo_id = $link->insert_id;
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
        if ($link->errno == 1062) {
            $response['message'] = 'Este CPF já está cadastrado no sistema.';
        } else {
            $response['message'] = 'Erro ao salvar no banco de dados.';
        }
    }
    $stmt->close();
} else {
    $response['message'] = 'Erro na preparação da consulta.';
}
$link->close();
echo json_encode($response);
?>