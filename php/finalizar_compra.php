<?php
// /php/finalizar_compra.php (Versão SIMPLIFICADA com GET)

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
// ... (seu código existente para pegar os dados e calcular os números da sorte continua igual)...
$vendedor_id = $_POST['vendedor_id'] ?? 0;
$valor_formatado = $_POST['valor'] ?? '0';
if (empty($cliente_id) || empty($vendedor_id) || empty($valor_formatado)) { /* ... */ exit; }
$result_config = $link->query("SELECT valor FROM configuracoes WHERE chave = 'sorteio_valor_base_extra'");
$valor_base_sorteio = $result_config->fetch_assoc()['valor'] ?? 50;
if ($valor_base_sorteio <= 0) { $valor_base_sorteio = 50; }
$valor_sem_ponto = str_replace('.', '', $valor_formatado);
$valor_para_banco = str_replace(',', '.', $valor_sem_ponto);
$usuario_id = 1;
$entradas_sorteio = 1 + floor($valor_para_banco / $valor_base_sorteio);
$numeros_da_sorte_gerados = [];

$link->begin_transaction();
try {
    // ... (seu código de inserção no banco continua igual) ...
    $sql_compra = "INSERT INTO compras (cliente_id, valor, vendedor_id, usuario_id) VALUES (?, ?, ?, ?)";
    $stmt_compra = $link->prepare($sql_compra);
    $stmt_compra->bind_param("idii", $cliente_id, $valor_para_banco, $vendedor_id, $usuario_id);
    $stmt_compra->execute();
    $compra_id = $link->insert_id;
    $stmt_compra->close();

    $sql_sorteio = "INSERT INTO sorteio (cliente_id, compra_id, usuario_id) VALUES (?, ?, ?)";
    $stmt_sorteio = $link->prepare($sql_sorteio);
    for ($i = 0; $i < $entradas_sorteio; $i++) {
        $stmt_sorteio->bind_param("iii", $cliente_id, $compra_id, $usuario_id);
        $stmt_sorteio->execute();
        $numeros_da_sorte_gerados[] = $link->insert_id;
    }
    $stmt_sorteio->close();
    
    // ================== INÍCIO DO NOVO BLOCO DE WEBHOOK (MÉTODO FÁCIL) ==================

    $sql_cliente = "SELECT nome_completo, cpf, whatsapp FROM clientes WHERE id = ?";
    $stmt_cliente = $link->prepare($sql_cliente);
    $stmt_cliente->bind_param("i", $cliente_id);
    $stmt_cliente->execute();
    $result_cliente = $stmt_cliente->get_result();
    $dados_cliente = $result_cliente->fetch_assoc();
    $stmt_cliente->close();

    if ($dados_cliente) {
        // 1. URL base do webhook
        $webhook_url_base = 'https://webhook.weagles.com.br/webhook/65a611a7-036b-43de-b712-0fe69ebb9452';

        // 2. Dados a serem enviados
        $dados_para_webhook = [
            'nome_cliente'      => $dados_cliente['nome_completo'],
            'whatsapp'          => $dados_cliente['whatsapp'],
            'cpf'               => $dados_cliente['cpf'],
            // Converte o array de números em uma string separada por vírgula (ex: "123,124,125")
            'numeros_da_sorte'  => implode(',', $numeros_da_sorte_gerados) 
        ];

        // 3. Monta a URL final com os dados (ex: ...?nome_cliente=Erick&whatsapp=...)
        $query_params = http_build_query($dados_para_webhook);
        $url_final = $webhook_url_base . '?' . $query_params;

        // 4. Dispara o webhook! 
        // A @ ignora qualquer erro caso a URL não responda, evitando que seu script quebre.
        @file_get_contents($url_final);
    }
    // =================== FIM DO BLOCO DE WEBHOOK ====================

    $link->commit();
    unset($_SESSION['vendedor_autenticado']);
    $response['status'] = 'success';
    $response['message'] = 'Compra registrada com sucesso!';
    $response['redirect'] = 'sucesso.php';

} catch (mysqli_sql_exception $exception) {
    $link->rollback();
    $response['message'] = 'Erro ao salvar os dados no banco.';
}

$link->close();
echo json_encode($response);
?>