<?php
// /php/realizar_sorteio.php (Versão com exclusão de cupons do ganhador)

session_start();
require_once "db_config.php";
header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Acesso não autorizado.'];

// Segurança: Apenas o Admin (CARGO = 1) pode realizar um sorteio.
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['usuario_cargo']) || $_SESSION['usuario_cargo'] != 1) {
    echo json_encode($response);
    exit;
}

$admin_id = $_SESSION['usuario_id'];
$winner_data = null;
$winner_id = null;

// --- INÍCIO DA TRANSAÇÃO ---
// Isso garante que ou todas as operações (sortear e deletar) funcionam, ou nenhuma funciona.
$link->begin_transaction();

try {
    // 1. SORTEIA UM GANHADOR
    // Sorteia UM cliente_id aleatoriamente da tabela de sorteio
    $sql_sorteio = "SELECT cliente_id FROM sorteio WHERE usuario_id = ? ORDER BY RAND() LIMIT 1";
    $stmt_sorteio = $link->prepare($sql_sorteio);
    $stmt_sorteio->bind_param("i", $admin_id);
    $stmt_sorteio->execute();
    $result_sorteio = $stmt_sorteio->get_result();

    if ($result_sorteio->num_rows > 0) {
        $sorteado = $result_sorteio->fetch_assoc();
        $winner_id = $sorteado['cliente_id'];
        $stmt_sorteio->close();

        // 2. BUSCA OS DADOS COMPLETOS DO GANHADOR
        $sql_cliente = "SELECT nome_completo, cpf, whatsapp FROM clientes WHERE id = ?";
        $stmt_cliente = $link->prepare($sql_cliente);
        $stmt_cliente->bind_param("i", $winner_id);
        $stmt_cliente->execute();
        $result_cliente = $stmt_cliente->get_result();
        if($result_cliente->num_rows > 0) {
            $winner_data = $result_cliente->fetch_assoc();
        }
        $stmt_cliente->close();

        // Se não encontrou os dados do cliente, lança um erro para cancelar a transação
        if (!$winner_data) {
            throw new Exception('Erro: Ganhador sorteado não encontrado na base de clientes.');
        }

        // 3. DELETA TODOS OS CUPONS DO GANHADOR
        // Esta é a nova regra de negócio!
        $sql_delete = "DELETE FROM sorteio WHERE cliente_id = ? AND usuario_id = ?";
        $stmt_delete = $link->prepare($sql_delete);
        $stmt_delete->bind_param("ii", $winner_id, $admin_id);
        $stmt_delete->execute();
        $stmt_delete->close();
        
        // 4. CONFIRMA A TRANSAÇÃO
        // Se chegamos até aqui, tudo deu certo. Confirma as operações.
        $link->commit();

        $response = [
            'status' => 'success',
            'ganhador' => $winner_data
        ];

    } else {
        // Não há cupons para sortear, então cancela a transação
        $link->rollback();
        $response['message'] = 'Não há nenhum número da sorte cadastrado para sortear!';
    }
} catch (mysqli_sql_exception $exception) {
    // Se qualquer um dos passos acima falhar, desfaz tudo
    $link->rollback();
    $response['message'] = 'Ocorreu um erro durante o sorteio. Tente novamente.';
    // Para depuração: error_log($exception->getMessage());
}

$link->close();
echo json_encode($response);
?>