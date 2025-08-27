<?php
session_start();
require_once "db_config.php";
header('Content-Type: application/json');

// Garante que apenas um usuário logado possa acessar estes dados
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Acesso não autorizado.']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$response = [];

// 1. Query para buscar o número de clientes cadastrados nos últimos 7 dias
$sql_clientes = "SELECT COUNT(id) as total_clientes FROM clientes WHERE usuario_id = ? AND data_cadastro >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
if ($stmt_clientes = $link->prepare($sql_clientes)) {
    $stmt_clientes->bind_param("i", $usuario_id);
    $stmt_clientes->execute();
    $result = $stmt_clientes->get_result()->fetch_assoc();
    // Se não houver resultado, o total é 0
    $response['total_clientes'] = $result['total_clientes'] ?? 0;
    $stmt_clientes->close();
}

// 2. Query para buscar a soma das vendas dos últimos 7 dias
$sql_vendas = "SELECT SUM(valor) as total_vendas FROM compras WHERE usuario_id = ? AND data_compra >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
if ($stmt_vendas = $link->prepare($sql_vendas)) {
    $stmt_vendas->bind_param("i", $usuario_id);
    $stmt_vendas->execute();
    $result = $stmt_vendas->get_result()->fetch_assoc();
    // Se não houver vendas, o total é 0.00
    $response['total_vendas'] = $result['total_vendas'] ?? '0.00';
    $stmt_vendas->close();
}

$response['status'] = 'success';
echo json_encode($response);

$link->close();
?>