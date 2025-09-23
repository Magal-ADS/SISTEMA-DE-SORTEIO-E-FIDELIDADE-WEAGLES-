<?php
// /php/finalizar_compra.php (VERSÃO CORRETA - SEM LOGIN NECESSÁRIO)

session_start();
require_once "db_config.php";
header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Ocorreu um erro desconhecido.'];

// A única coisa que precisamos da sessão é o ID do cliente, definido após a busca de CPF.
$cliente_id = $_SESSION['cliente_id'] ?? 0;

// O resto dos dados vem do formulário da página dados_compra.php
$vendedor_id = $_POST['vendedor_id'] ?? 0;
$valor_formatado = $_POST['valor'] ?? '0';


// A nova validação, muito mais simples:
if (empty($cliente_id) || empty($vendedor_id) || empty($valor_formatado)) {
    $response['message'] = 'Dados da compra inválidos. Cliente, vendedor ou valor não foram informados.';
    echo json_encode($response);
    exit;
}

// Lógica de negócio (sem alterações)
$result_config = pg_query($link, "SELECT valor FROM configuracoes WHERE chave = 'sorteio_valor_base_extra'");
$valor_base_sorteio = pg_fetch_assoc($result_config)['valor'] ?? 50;
if ($valor_base_sorteio <= 0) { $valor_base_sorteio = 50; }
$valor_sem_ponto = str_replace('.', '', $valor_formatado);
$valor_para_banco = str_replace(',', '.', $valor_sem_ponto);
$entradas_sorteio = 1 + floor($valor_para_banco / $valor_base_sorteio);
$numeros_da_sorte_gerados = [];

pg_query($link, "BEGIN");

try {
    // Inserção na tabela de compras: tanto 'vendedor_id' quanto 'usuario_id' usam o ID do vendedor selecionado no formulário.
    $sql_compra = "INSERT INTO compras (cliente_id, valor, vendedor_id, usuario_id) VALUES ($1, $2, $3, $4) RETURNING id";
    $result_compra = pg_query_params($link, $sql_compra, array($cliente_id, $valor_para_banco, $vendedor_id, $vendedor_id));
    
    if (!$result_compra) { throw new Exception(pg_last_error($link)); }
    $compra_id = pg_fetch_assoc($result_compra)['id'];

    // Inserção na tabela de sorteio: o 'usuario_id' também é o da vendedora que fez a venda.
    $sql_sorteio = "INSERT INTO sorteio (cliente_id, compra_id, usuario_id) VALUES ($1, $2, $3) RETURNING id";
    for ($i = 0; $i < $entradas_sorteio; $i++) {
        $result_sorteio = pg_query_params($link, $sql_sorteio, array($cliente_id, $compra_id, $vendedor_id));
        if (!$result_sorteio) { throw new Exception(pg_last_error($link)); }
        $numeros_da_sorte_gerados[] = pg_fetch_assoc($result_sorteio)['id'];
    }

    // Busca os dados do cliente para o webhook (inalterado)
    $sql_cliente = "SELECT nome_completo, cpf, whatsapp FROM clientes WHERE id = $1";
    $result_cliente = pg_query_params($link, $sql_cliente, array($cliente_id));
    $dados_cliente = pg_fetch_assoc($result_cliente);

    if ($dados_cliente) {
        // Bloco de Webhook (sem alterações)
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

    pg_query($link, "COMMIT");

    // Limpa a sessão do cliente para forçar uma nova busca no próximo atendimento
    unset($_SESSION['cliente_id']);
    unset($_SESSION['cliente_nome']);
    unset($_SESSION['cpf_cliente']);
    
    // Podemos remover o 'vendedor_autenticado' já que não é um login real
    unset($_SESSION['vendedor_autenticado']);

    $response['status'] = 'success';
    $response['message'] = 'Compra registrada com sucesso!';
    $response['redirect'] = 'sucesso.php';

} catch (Exception $e) {
    pg_query($link, "ROLLBACK");
    $response['message'] = 'Erro ao salvar os dados no banco: ' . $e->getMessage();
}

pg_close($link);
echo json_encode($response);
?>