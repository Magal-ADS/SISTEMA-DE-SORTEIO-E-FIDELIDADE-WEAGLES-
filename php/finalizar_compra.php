<?php
// /php/finalizar_compra.php

session_start();
require_once "db_config.php";
header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Ocorreu um erro desconhecido.'];

if (!isset($_SESSION['vendedor_autenticado']) || $_SESSION['vendedor_autenticado'] !== true) {
    $response['message'] = 'Acesso não autorizado.';
    echo json_encode($response);
    exit;
}

$cliente_id = $_POST['cliente_id'] ?? 0;
$vendedor_id = $_POST['vendedor_id'] ?? 0;
$valor_formatado = $_POST['valor'] ?? '0';

if (empty($cliente_id) || empty($vendedor_id) || empty($valor_formatado)) {
    $response['message'] = 'Dados da compra inválidos.';
    echo json_encode($response);
    exit;
}

// LÓGICA DE NEGÓCIO (INTACTA)
$result_config = pg_query($link, "SELECT valor FROM configuracoes WHERE chave = 'sorteio_valor_base_extra'");
$valor_base_sorteio = pg_fetch_assoc($result_config)['valor'] ?? 50;
if ($valor_base_sorteio <= 0) { $valor_base_sorteio = 50; }
$valor_sem_ponto = str_replace('.', '', $valor_formatado);
$valor_para_banco = str_replace(',', '.', $valor_sem_ponto);
$usuario_id = 1;
$entradas_sorteio = 1 + floor($valor_para_banco / $valor_base_sorteio);
$numeros_da_sorte_gerados = [];

// ALTERADO: Início da transação no Postgres
pg_query($link, "BEGIN");
$transaction_success = true;

// Prepara as consultas fora do loop para melhor performance
$sql_compra = "INSERT INTO compras (cliente_id, valor, vendedor_id, usuario_id) VALUES ($1, $2, $3, $4) RETURNING id";
pg_prepare($link, "insert_compra", $sql_compra);

$sql_sorteio = "INSERT INTO sorteio (cliente_id, compra_id, usuario_id) VALUES ($1, $2, $3) RETURNING id";
pg_prepare($link, "insert_sorteio", $sql_sorteio);

// Inserção na tabela de compras
$result_compra = pg_execute($link, "insert_compra", array($cliente_id, $valor_para_banco, $vendedor_id, $usuario_id));
if (!$result_compra) {
    $transaction_success = false;
} else {
    $compra_id = pg_fetch_assoc($result_compra)['id'];

    // Loop de inserção na tabela de sorteio
    for ($i = 0; $i < $entradas_sorteio; $i++) {
        $result_sorteio = pg_execute($link, "insert_sorteio", array($cliente_id, $compra_id, $usuario_id));
        if (!$result_sorteio) {
            $transaction_success = false;
            break; // Sai do loop se um dos inserts falhar
        }
        $numeros_da_sorte_gerados[] = pg_fetch_assoc($result_sorteio)['id'];
    }
}

// Se todas as inserções deram certo, busca os dados do cliente para o webhook
if ($transaction_success) {
    $sql_cliente = "SELECT nome_completo, cpf, whatsapp FROM clientes WHERE id = $1";
    pg_prepare($link, "get_cliente", $sql_cliente);
    $result_cliente = pg_execute($link, "get_cliente", array($cliente_id));
    $dados_cliente = pg_fetch_assoc($result_cliente);

    if ($dados_cliente) {
        // Bloco de Webhook (INTACTO)
        $webhook_url_base = 'https://webhook.weagles.com.br/webhook/65a611a7-036b-43de-b712-0fe69ebb9452';
        $dados_para_webhook = [
            'nome_cliente'     => $dados_cliente['nome_completo'],
            'whatsapp'         => $dados_cliente['whatsapp'],
            'cpf'              => $dados_cliente['cpf'],
            'numeros_da_sorte' => implode(',', $numeros_da_sorte_gerados) 
        ];
        $query_params = http_build_query($dados_para_webhook);
        $url_final = $webhook_url_base . '?' . $query_params;
        @file_get_contents($url_final);
    }

    // ALTERADO: Finaliza a transação com COMMIT
    pg_query($link, "COMMIT");
    unset($_SESSION['vendedor_autenticado']);
    $response['status'] = 'success';
    $response['message'] = 'Compra registrada com sucesso!';
    $response['redirect'] = 'sucesso.php';

} else {
    // ALTERADO: Finaliza a transação com ROLLBACK
    pg_query($link, "ROLLBACK");
    $response['message'] = 'Erro ao salvar os dados no banco.';
}

pg_close($link);
echo json_encode($response);
?>