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


$sql = "INSERT INTO clientes (cpf, nome_completo, whatsapp, data_nascimento, usuario_id) VALUES (?, ?, ?, ?, ?)";

if ($stmt = $link->prepare($sql)) {
    $stmt->bind_param("ssssi", $cpf, $nome, $whatsapp, $nascimento_para_banco, $usuario_id);
    
    if ($stmt->execute()) {
        
        // ==========================================================
        //  AQUI ESTÁ A CORREÇÃO MÁGICA
        //  Após cadastrar, salvamos os dados do novo cliente na sessão
        //  para que a próxima página saiba quem ele é.
        // ==========================================================
        $_SESSION['cliente_id'] = $link->insert_id; // Pega o ID do cliente que acabou de ser criado
        $_SESSION['cliente_nome'] = $nome;          // Salva o nome dele na sessão

        $response = ['status' => 'success', 'message' => 'Cliente cadastrado com sucesso!'];
    } else {
        if ($link->errno == 1062) {
             $response['message'] = 'Este CPF já está cadastrado.';
        } else {
             $response['message'] = 'Erro ao cadastrar o cliente.';
        }
    }
    $stmt->close();
} else {
    $response['message'] = 'Erro na preparação da consulta.';
}

$link->close();
echo json_encode($response);
?>