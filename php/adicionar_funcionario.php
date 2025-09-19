<?php
// /php/adicionar_funcionario.php (VERSÃO COM VALIDAÇÃO DE CPF)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "db_config.php";
header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Ocorreu um erro desconhecido.'];

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

// =================== NOVA VALIDAÇÃO DE CPF ADICIONADA AQUI ===================
if (strlen($cpf_limpo) != 11) {
    $response['message'] = 'O CPF fornecido é inválido. Ele deve conter 11 dígitos.';
    echo json_encode($response);
    exit;
}
// ===========================================================================

$hash_senha = password_hash($senha, PASSWORD_DEFAULT);

$sql = "INSERT INTO usuarios (nome, cpf, senha, cargo, ativo) VALUES ($1, $2, $3, $4, TRUE) RETURNING id";

$stmt = pg_prepare($link, "add_funcionario_query_v2", $sql);

if ($stmt) {
    $result = pg_execute($link, "add_funcionario_query_v2", [$nome, $cpf_limpo, $hash_senha, $cargo]);
    
    if ($result) {
        $novo_id = pg_fetch_result($result, 0, 'id');
        $response = [
            'status' => 'success',
            'message' => 'Funcionário adicionado com sucesso!',
            'novoFuncionario' => [
                'id' => $novo_id,
                'nome' => $nome,
                'cpf' => $cpf_input, // Retorna o CPF com máscara para a tela
                'cargo' => ($cargo == 2) ? 'Vendedor' : 'Administrador'
            ]
        ];
    } else {
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