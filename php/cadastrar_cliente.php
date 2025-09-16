<?php
// /php/cadastrar_cliente.php

session_start();
require_once "db_config.php";
header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Ocorreu um erro desconhecido.'];

// Associa o cliente ao usuário/loja padrão (ID 1)
$usuario_id = 1; 

$cpf = $_POST['cpf'] ?? ''; 
$nome = trim($_POST['nome'] ?? '');
$whatsapp = trim($_POST['whatsapp'] ?? '');
$nascimento_br = trim($_POST['nascimento'] ?? ''); 

if (empty($cpf) || empty($nome) || empty($whatsapp) || empty($nascimento_br)) {
    $response['message'] = 'Todos os campos são obrigatórios.';
    echo json_encode($response);
    exit;
}

$date_obj = DateTime::createFromFormat('d/m/Y', $nascimento_br);
if (!$date_obj || $date_obj->format('d/m/Y') !== $nascimento_br) {
    $response['message'] = 'Data de nascimento inválida. Use o formato DD/MM/AAAA.';
    echo json_encode($response);
    exit;
}
$nascimento_para_banco = $date_obj->format('Y-m-d');


// ALTERADO: SQL com placeholders do Postgres e 'RETURNING id'
$sql = "INSERT INTO clientes (cpf, nome_completo, whatsapp, data_nascimento, usuario_id) VALUES ($1, $2, $3, $4, $5) RETURNING id";

// ALTERADO: Bloco de consulta para usar as funções do Postgres
$stmt = pg_prepare($link, "cadastrar_cliente_query", $sql);
if ($stmt) {
    $result = pg_execute($link, "cadastrar_cliente_query", array($cpf, $nome, $whatsapp, $nascimento_para_banco, $usuario_id));
    
    if ($result && pg_num_rows($result) > 0) {
        
        // Pega o ID do cliente que o "RETURNING id" devolveu
        $row = pg_fetch_assoc($result);
        $_SESSION['cliente_id'] = $row['id'];
        $_SESSION['cliente_nome'] = $nome;

        $response = ['status' => 'success', 'message' => 'Cliente cadastrado com sucesso!'];
    } else {
        // ALTERADO: Tratamento de erro para chave duplicada no Postgres
        if (pg_result_error_field($result, PGSQL_DIAG_SQLSTATE) == "23505") {
             $response['message'] = 'Este CPF já está cadastrado.';
        } else {
             $response['message'] = 'Erro ao cadastrar o cliente.';
        }
    }
} else {
    $response['message'] = 'Erro na preparação da consulta.';
}

pg_close($link);
echo json_encode($response);
?>