<?php
session_start();
require_once "db_config.php";
header('Content-Type: application/json');

// Lógica para definir de qual usuário (loja) buscar os dados
if (isset($_SESSION['usuario_id'])) {
    $usuario_id = $_SESSION['usuario_id'];
} else {
    $usuario_id = 1; // Padrão
}

$cpf = $_GET['cpf'] ?? '';

if (empty($cpf)) {
    echo json_encode(['status' => 'error', 'message' => 'CPF não fornecido.']);
    exit;
}

// Busca dados do cliente, garantindo que ele pertence ao usuário (loja) correto
$sql_cliente = "SELECT id, nome_completo FROM clientes WHERE cpf = ? AND usuario_id = ?";
$stmt = $link->prepare($sql_cliente);
$stmt->bind_param("si", $cpf, $usuario_id);
$stmt->execute();
$result_cliente = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$result_cliente) {
    // Se não encontrou o cliente para este usuário, pode ser que ele ainda não exista
    // Então não retornamos um erro, apenas não teremos o nome do cliente
    // A busca de vendedores continua
}

// Busca lista de vendedores APENAS do usuário (loja) correto
$sql_vendedores = "SELECT nome FROM vendedores WHERE usuario_id = ? ORDER BY nome ASC";
$stmt_vendedores = $link->prepare($sql_vendedores);
$stmt_vendedores->bind_param("i", $usuario_id);
$stmt_vendedores->execute();
$result_vendedores = $stmt_vendedores->get_result();

$vendedores = [];
while($row = $result_vendedores->fetch_assoc()){
    $vendedores[] = $row;
}

echo json_encode([
    'status' => 'success', 
    'cliente' => $result_cliente, // Pode ser null se o cliente for novo
    'vendedores' => $vendedores
]);

$link->close();
?>