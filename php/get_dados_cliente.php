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

// ALTERADO: Busca dados do cliente usando funções do Postgres
$sql_cliente = "SELECT id, nome_completo FROM clientes WHERE cpf = $1 AND usuario_id = $2";
$result_cliente = null; // Inicia como nulo

$stmt_cliente = pg_prepare($link, "get_cliente_query", $sql_cliente);
if ($stmt_cliente) {
    $result = pg_execute($link, "get_cliente_query", array($cpf, $usuario_id));
    if ($result && pg_num_rows($result) > 0) {
        $result_cliente = pg_fetch_assoc($result);
    }
}

// ALTERADO: Busca lista de vendedores na tabela 'usuarios' com CARGO = 2
// Como não há parâmetros, uma consulta direta é mais simples
$sql_vendedores = "SELECT id, nome FROM usuarios WHERE CARGO = 2 ORDER BY nome ASC";
$result_vendedores = pg_query($link, $sql_vendedores);

$vendedores = [];
if ($result_vendedores) {
    // O loop para buscar os dados continua com a função do Postgres
    while($row = pg_fetch_assoc($result_vendedores)){
        $vendedores[] = $row;
    }
}

echo json_encode([
    'status' => 'success', 
    'cliente' => $result_cliente, // Pode ser null se o cliente for novo
    'vendedores' => $vendedores
]);

pg_close($link);
?>