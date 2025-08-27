<?php
// /php/adicionar_funcionario.php

session_start();
require_once "db_config.php";
header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Ocorreu um erro desconhecido.'];

// Segurança: Apenas um admin logado pode adicionar outros usuários.
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['usuario_cargo']) || $_SESSION['usuario_cargo'] != 1) {
    $response['message'] = 'Acesso não autorizado.';
    echo json_encode($response);
    exit;
}

// 1. Recebe e valida os dados do formulário
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

// 2. Trata os dados
$cpf_limpo = preg_replace('/[^0-9]/', '', $cpf_input);
$hash_senha = password_hash($senha, PASSWORD_DEFAULT); // Criptografa a senha

// 3. Insere no banco de dados
$sql = "INSERT INTO usuarios (nome, cpf, senha, CARGO) VALUES (?, ?, ?, ?)";

if ($stmt = $link->prepare($sql)) {
    $stmt->bind_param("sssi", $nome, $cpf_limpo, $hash_senha, $cargo);
    
    if ($stmt->execute()) {
        $novo_id = $link->insert_id; // Pega o ID do funcionário recém-criado
        $response = [
            'status' => 'success',
            'message' => 'Funcionário adicionado com sucesso!',
            // Devolve os dados do novo funcionário para o front-end atualizar a tabela
            'novoFuncionario' => [
                'id' => $novo_id,
                'nome' => $nome,
                'cpf' => $cpf_input, // Devolve o CPF com máscara para exibição
                'cargo' => ($cargo == 2) ? 'Vendedor' : 'Administrador'
            ]
        ];
    } else {
        if ($link->errno == 1062) { // Erro de entrada duplicada (CPF ou CNPJ)
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