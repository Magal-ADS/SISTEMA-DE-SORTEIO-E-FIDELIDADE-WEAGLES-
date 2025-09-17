<?php
// /php/verificar_cpf.php (Versão PostgreSQL Corrigida)

session_start();
header('Content-Type: application/json');
require_once "db_config.php";

$response = ['status' => 'error', 'message' => 'Ocorreu um erro desconhecido.'];

$cpf_formatado = $_POST['cpf'] ?? '';

if (empty($cpf_formatado)) {
    $response['message'] = 'CPF não fornecido.';
    echo json_encode($response);
    exit;
}

// =================== CORREÇÃO APLICADA AQUI ===================
// Adicionamos esta linha para remover os pontos e o traço do CPF.
$cpf_limpo = preg_replace('/[^0-9]/', '', $cpf_formatado);
// =============================================================

$sql = "SELECT id, nome_completo FROM clientes WHERE cpf = $1";

$stmt = pg_prepare($link, "verificar_cpf_query", $sql);

if ($stmt) {
    // Agora, usamos o CPF limpo para a busca
    $result = pg_execute($link, "verificar_cpf_query", [$cpf_limpo]);

    if ($result) {
        if (pg_num_rows($result) > 0) {
            // Cliente EXISTE
            $cliente = pg_fetch_assoc($result);
            
            $_SESSION['cliente_id'] = $cliente['id'];
            $_SESSION['cliente_nome'] = $cliente['nome_completo'];
            $_SESSION['cpf_cliente'] = $cpf_formatado; // Salva o formatado para exibição, se necessário
            
            $response = ['status' => 'exists', 'redirect' => 'confirmacao_cliente.php'];
        } else {
            // Cliente NÃO EXISTE
            $_SESSION['cpf_digitado'] = $cpf_formatado; // Salva o formatado para usar no cadastro
            
            $response = ['status' => 'not_exists', 'redirect' => 'cadastro.php'];
        }
    } else {
        $response['message'] = "Erro ao executar a consulta.";
    }
} else {
    $response['message'] = "Erro na preparação da consulta.";
}

pg_close($link);

echo json_encode($response);
?>