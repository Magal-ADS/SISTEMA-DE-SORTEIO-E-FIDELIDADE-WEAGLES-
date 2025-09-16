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
// ALTERADO: Sintaxe de data e placeholder
$sql_clientes = "SELECT COUNT(id) as total_clientes FROM clientes WHERE usuario_id = $1 AND data_cadastro >= NOW() - INTERVAL '7 days'";

// ALTERADO: Funções de consulta do Postgres
$stmt_clientes = pg_prepare($link, "dashboard_clientes", $sql_clientes);
if ($stmt_clientes) {
    $result = pg_execute($link, "dashboard_clientes", array($usuario_id));
    if($result) {
        $row = pg_fetch_assoc($result);
        $response['total_clientes'] = $row['total_clientes'] ?? 0;
    }
}

// 2. Query para buscar a soma das vendas dos últimos 7 dias
// ALTERADO: Sintaxe de data e placeholder
$sql_vendas = "SELECT SUM(valor) as total_vendas FROM compras WHERE usuario_id = $1 AND data_compra >= NOW() - INTERVAL '7 days'";

// ALTERADO: Funções de consulta do Postgres
$stmt_vendas = pg_prepare($link, "dashboard_vendas", $sql_vendas);
if ($stmt_vendas) {
    $result = pg_execute($link, "dashboard_vendas", array($usuario_id));
    if($result) {
        $row = pg_fetch_assoc($result);
        $response['total_vendas'] = $row['total_vendas'] ?? '0.00';
    }
}

$response['status'] = 'success';
echo json_encode($response);

pg_close($link);
?>