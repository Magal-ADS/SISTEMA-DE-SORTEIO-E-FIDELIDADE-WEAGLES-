<?php
// /php/verificar_cpf.php (Versão PostgreSQL)

session_start();
header('Content-Type: application/json');
require_once "db_config.php"; // Já está configurado para PostgreSQL

$response = ['status' => 'error', 'message' => 'Ocorreu um erro desconhecido.'];

$cpf = $_POST['cpf'] ?? '';

if (empty($cpf)) {
    $response['message'] = 'CPF não fornecido.';
    echo json_encode($response);
    exit;
}

// 1. SQL com placeholder do PostgreSQL ($1)
$sql = "SELECT id, nome_completo FROM clientes WHERE cpf = $1";

// 2. Prepara a consulta com pg_prepare
$stmt = pg_prepare($link, "verificar_cpf_query", $sql);

if ($stmt) {
    // 3. Executa a consulta com pg_execute
    $result = pg_execute($link, "verificar_cpf_query", [$cpf]);

    if ($result) {
        // 4. Verifica o número de linhas com pg_num_rows
        if (pg_num_rows($result) > 0) {
            // Cliente EXISTE
            // 5. Busca o resultado com pg_fetch_assoc
            $cliente = pg_fetch_assoc($result);
            
            // Lógica de sessão (inalterada)
            $_SESSION['cliente_id'] = $cliente['id'];
            $_SESSION['cliente_nome'] = $cliente['nome_completo'];
            $_SESSION['cpf_cliente'] = $cpf;
            
            $response = ['status' => 'exists', 'redirect' => 'confirmacao_cliente.php'];
        } else {
            // Cliente NÃO EXISTE
            // Lógica de sessão (inalterada)
            $_SESSION['cpf_digitado'] = $cpf;
            
            $response = ['status' => 'not_exists', 'redirect' => 'cadastro.php'];
        }
    } else {
        $response['message'] = "Erro ao executar a consulta.";
    }
} else {
    $response['message'] = "Erro na preparação da consulta.";
}

// 6. Fecha a conexão com pg_close
pg_close($link);

echo json_encode($response);
?>