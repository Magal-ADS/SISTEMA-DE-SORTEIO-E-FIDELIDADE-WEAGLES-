<?php
// /php/verificar_senha_vendedor.php (Versão PostgreSQL)

session_start();
require_once "db_config.php";
header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Ocorreu um erro.'];

$vendedor_id = $_POST['vendedor_id'] ?? 0;
$senha_vendedor = $_POST['senha_vendedor'] ?? '';

if (empty($vendedor_id) || empty($senha_vendedor)) {
    $response['message'] = 'Selecione o vendedor e digite a senha.';
    echo json_encode($response);
    exit;
}

// 1. SQL com placeholder do PostgreSQL ($1)
$sql = "SELECT senha FROM usuarios WHERE id = $1 AND CARGO = 2";

// 2. Prepara e executa a consulta com as funções pg_*
$stmt = pg_prepare($link, "verificar_senha_vendedor_query", $sql);

if ($stmt) {
    $result = pg_execute($link, "verificar_senha_vendedor_query", [$vendedor_id]);

    // 3. Verifica o número de linhas com pg_num_rows
    if ($result && pg_num_rows($result) === 1) {
        $vendedor = pg_fetch_assoc($result);

        // A função password_verify é do PHP, então não muda
        if (password_verify($senha_vendedor, $vendedor['senha'])) {
            // SUCESSO!
            $_SESSION['vendedor_autenticado_id'] = $vendedor_id;
            
            $response['status'] = 'success';
            unset($response['message']);

        } else {
            // Senha incorreta
            $response['message'] = 'Senha do vendedor incorreta.';
        }
    } else {
        // Vendedor não encontrado
        $response['message'] = 'Vendedor não encontrado.';
    }
} else {
    $response['message'] = 'Erro na preparação da consulta.';
}


// 4. Fecha a conexão com pg_close
pg_close($link);

echo json_encode($response);
?>