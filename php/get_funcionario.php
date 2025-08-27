<?php
// /php/get_funcionario.php

session_start();
require_once "db_config.php";
header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Ocorreu um erro.'];

if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['usuario_cargo']) || $_SESSION['usuario_cargo'] != 1) {
    $response['message'] = 'Acesso não autorizado.';
    echo json_encode($response);
    exit;
}

$id = $_GET['id'] ?? 0;

if (empty($id)) {
    $response['message'] = 'ID do funcionário não fornecido.';
    echo json_encode($response);
    exit;
}

$sql = "SELECT nome, cpf, CARGO FROM usuarios WHERE id = ?";
if ($stmt = $link->prepare($sql)) {
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($funcionario = $result->fetch_assoc()) {
        $response = [
            'status' => 'success',
            'funcionario' => $funcionario
        ];
    } else {
        $response['message'] = 'Funcionário não encontrado.';
    }
    $stmt->close();
}

$link->close();
echo json_encode($response);
?>