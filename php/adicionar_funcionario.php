<?php
// /php/adicionar_funcionario.php (VERSÃO FINAL E CORRIGIDA)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "db_config.php";
header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Ocorreu um erro desconhecido.'];

// Bloco de segurança padronizado
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['cargo']) || $_SESSION['cargo'] != 1) {
    $response['message'] = 'Acesso não autorizado.';
    echo json_encode($response);
    exit;
}

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

// Lógica de inserção convertida para PostgreSQL
$sql = "INSERT INTO usuarios (nome, cpf, senha, cargo) VALUES ($1, $2, $3, $4) RETURNING id";

$stmt = pg_prepare($link, "add_funcionario_query", $sql);

if ($stmt) {
    $result = pg_execute($link, "add_funcionario_query", [$nome, $cpf_limpo, $hash_senha, $cargo]);
    
    if ($result) {
        $novo_id = pg_fetch_result($result, 0, 'id');
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
        // Verifica se o erro é de CPF duplicado
        if (strpos(pg_last_error($link), 'usuarios_cpf_key') !== false) {
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