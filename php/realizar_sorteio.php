<?php
// /php/realizar_sorteio.php (Versão PostgreSQL)

session_start();
require_once "db_config.php";
header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Acesso não autorizado.'];

// Verificação de segurança (inalterada)
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['cargo']) || $_SESSION['cargo'] != 1) {
    echo json_encode($response);
    exit;
}

$admin_id = $_SESSION['usuario_id'];
$winner_data = null;
$winner_id = null;

// 1. CONTROLE DE TRANSAÇÃO: Inicia a transação com pg_query
pg_query($link, "BEGIN");

try {
    // 2. SORTEIA UM GANHADOR (SQL alterado)
    // MUDANÇA: MySQL usa RAND(), PostgreSQL usa RANDOM()
    $sql_sorteio = "SELECT cliente_id FROM sorteio WHERE usuario_id = $1 ORDER BY RANDOM() LIMIT 1";
    
    $stmt_sorteio = pg_prepare($link, "realizar_sorteio_query", $sql_sorteio);
    if (!$stmt_sorteio) { throw new Exception('Falha ao preparar a consulta do sorteio.'); }

    $result_sorteio = pg_execute($link, "realizar_sorteio_query", [$admin_id]);
    if (!$result_sorteio) { throw new Exception('Falha ao executar a consulta do sorteio.'); }

    // pg_num_rows é o equivalente a $result->num_rows
    if (pg_num_rows($result_sorteio) > 0) {
        $sorteado = pg_fetch_assoc($result_sorteio);
        $winner_id = $sorteado['cliente_id'];

        // 3. BUSCA OS DADOS COMPLETOS DO GANHADOR
        $sql_cliente = "SELECT nome_completo, cpf, whatsapp FROM clientes WHERE id = $1";
        $stmt_cliente = pg_prepare($link, "buscar_ganhador_query", $sql_cliente);
        if (!$stmt_cliente) { throw new Exception('Falha ao preparar a busca pelo ganhador.'); }
        
        $result_cliente = pg_execute($link, "buscar_ganhador_query", [$winner_id]);
        if (!$result_cliente) { throw new Exception('Falha ao buscar os dados do ganhador.'); }

        if(pg_num_rows($result_cliente) > 0) {
            $winner_data = pg_fetch_assoc($result_cliente);
        }
        
        if (!$winner_data) {
            throw new Exception('Ganhador sorteado não encontrado na base de clientes.');
        }

        // 4. DELETA TODOS OS CUPONS DO GANHADOR
        $sql_delete = "DELETE FROM sorteio WHERE cliente_id = $1 AND usuario_id = $2";
        $stmt_delete = pg_prepare($link, "deletar_cupons_query", $sql_delete);
        if (!$stmt_delete) { throw new Exception('Falha ao preparar a deleção de cupons.'); }

        $result_delete = pg_execute($link, "deletar_cupons_query", [$winner_id, $admin_id]);
        if (!$result_delete) { throw new Exception('Falha ao deletar os cupons do ganhador.'); }
        
        // 5. CONFIRMA A TRANSAÇÃO: Commita as mudanças
        pg_query($link, "COMMIT");

        $response = [
            'status' => 'success',
            'ganhador' => $winner_data
        ];

    } else {
        // Se não há participantes, desfaz a transação (ROLLBACK)
        pg_query($link, "ROLLBACK");
        $response['message'] = 'Não há nenhum número da sorte cadastrado para sortear!';
    }
} catch (Exception $exception) {
    // Em qualquer erro, desfaz a transação (ROLLBACK)
    pg_query($link, "ROLLBACK");
    $response['message'] = 'Ocorreu um erro durante o sorteio: ' . $exception->getMessage();
}

// Fecha a conexão
pg_close($link);
echo json_encode($response);
?>