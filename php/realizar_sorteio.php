<?php
// /php/realizar_sorteio.php (VERSÃO CORRIGIDA)

session_start();
require_once "db_config.php";
header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Acesso não autorizado.'];

if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['cargo']) || $_SESSION['cargo'] != 1) {
    echo json_encode($response);
    exit;
}

// O ID do admin não é mais necessário para esta lógica.
// $admin_id = $_SESSION['usuario_id']; 
$winner_data = null;
$winner_id = null;

pg_query($link, "BEGIN");

try {
    // =================== CORREÇÃO 1 ===================
    // Removemos o "WHERE usuario_id = $1"
    $sql_sorteio = "SELECT cliente_id FROM sorteio ORDER BY RANDOM() LIMIT 1";
    
    $stmt_sorteio = pg_prepare($link, "realizar_sorteio_query", $sql_sorteio);
    if (!$stmt_sorteio) { throw new Exception('Falha ao preparar a consulta do sorteio.'); }

    // Executa sem parâmetros, pois removemos o $admin_id
    $result_sorteio = pg_execute($link, "realizar_sorteio_query", []); 
    
    if (!$result_sorteio) { throw new Exception('Falha ao executar a consulta do sorteio.'); }

    if (pg_num_rows($result_sorteio) > 0) {
        $sorteado = pg_fetch_assoc($result_sorteio);
        $winner_id = $sorteado['cliente_id'];

        // Busca o nome do ganhador (lógica inalterada e correta)
        $sql_cliente = "SELECT nome_completo FROM clientes WHERE id = $1";
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

        // =================== CORREÇÃO 2 ===================
        // Removemos o "AND usuario_id = $2"
        $sql_delete = "DELETE FROM sorteio WHERE cliente_id = $1";
        
        $stmt_delete = pg_prepare($link, "deletar_cupons_query", $sql_delete);
        if (!$stmt_delete) { throw new Exception('Falha ao preparar a deleção de cupons.'); }

        // Executa apenas com o $winner_id
        $result_delete = pg_execute($link, "deletar_cupons_query", [$winner_id]); 
        
        if (!$result_delete) { throw new Exception('Falha ao deletar os cupons do ganhador.'); }
        
        pg_query($link, "COMMIT");

        $response = [
            'status' => 'success',
            'ganhador' => $winner_data
        ];

    } else {
        pg_query($link, "ROLLBACK");
        // Mensagem de erro atualizada para refletir a lógica correta
        $response['message'] = 'Não há nenhum número da sorte na urna para sortear!';
    }
} catch (Exception $exception) {
    pg_query($link, "ROLLBACK");
    $response['message'] = 'Ocorreu um erro durante o sorteio: ' . $exception->getMessage();
}

pg_close($link);
echo json_encode($response);
?>