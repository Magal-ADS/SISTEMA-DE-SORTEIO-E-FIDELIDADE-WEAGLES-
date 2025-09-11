<?php
// /php/realizar_sorteio.php

session_start();
require_once "db_config.php";
header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Acesso não autorizado.'];

// CORREÇÃO AQUI: Verificando a variável de sessão correta -> $_SESSION['cargo']
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['cargo']) || $_SESSION['cargo'] != 1) {
    echo json_encode($response);
    exit;
}

$admin_id = $_SESSION['usuario_id'];
$winner_data = null;
$winner_id = null;

$link->begin_transaction();
try {
    // 1. SORTEIA UM GANHADOR
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

        if (!$winner_data) {
            throw new Exception('Ganhador sorteado não encontrado na base de clientes.');
        }

        // 3. DELETA TODOS OS CUPONS DO GANHADOR
        $sql_delete = "DELETE FROM sorteio WHERE cliente_id = ? AND usuario_id = ?";
        $stmt_delete = $link->prepare($sql_delete);
        $stmt_delete->bind_param("ii", $winner_id, $admin_id);
        $stmt_delete->execute();
        $stmt_delete->close();
        
        // 4. CONFIRMA A TRANSAÇÃO
        $link->commit();

        $response = [
            'status' => 'success',
            'ganhador' => $winner_data
        ];

    } else {
        $link->rollback();
        $response['message'] = 'Não há nenhum número da sorte cadastrado para sortear!';
    }
} catch (Exception $exception) {
    $link->rollback();
    $response['message'] = 'Ocorreu um erro durante o sorteio: ' . $exception->getMessage();
}

$link->close();
echo json_encode($response);
?>