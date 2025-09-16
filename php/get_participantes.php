<?php
// /php/get_participantes.php (Versão PostgreSQL)

session_start();
require_once "db_config.php"; // Este arquivo já usa a conexão pg_connect
header('Content-Type: application/json');

// 1. Bloco de segurança (lógica inalterada)
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['cargo']) || $_SESSION['cargo'] != 1) {
    echo json_encode(['status' => 'error', 'message' => 'Acesso não autorizado.', 'participantes' => []]);
    exit;
}

$admin_id = $_SESSION['usuario_id'];
$participantes = [];

// 2. Query SQL adaptada para placeholders do PostgreSQL ($1, $2, etc.)
$sql = "SELECT DISTINCT c.nome_completo 
        FROM clientes c
        JOIN sorteio s ON c.id = s.cliente_id
        WHERE s.usuario_id = $1"; // <-- MUDANÇA: Placeholder '?' virou '$1'

// 3. Preparação e execução da consulta com funções pg_*
$stmt = pg_prepare($link, "get_participantes_query", $sql);

if ($stmt) {
    // Executa a query preparada, passando os parâmetros em um array
    $result = pg_execute($link, "get_participantes_query", [$admin_id]);

    if ($result) {
        // Itera sobre os resultados da mesma forma
        while ($row = pg_fetch_assoc($result)) {
            $participantes[] = $row['nome_completo'];
        }
        
        echo json_encode(['status' => 'success', 'participantes' => $participantes]);

    } else {
        // Erro na execução da consulta
        echo json_encode(['status' => 'error', 'message' => 'Erro ao executar a consulta.', 'participantes' => []]);
    }

} else {
    // Erro na preparação da consulta
    echo json_encode(['status' => 'error', 'message' => 'Erro na preparação da consulta ao banco de dados.', 'participantes' => []]);
}

// 4. Fecha a conexão com o PostgreSQL
pg_close($link);
?>