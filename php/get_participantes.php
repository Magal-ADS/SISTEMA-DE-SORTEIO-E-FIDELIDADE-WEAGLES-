<?php
// /php/get_participantes.php (VERSÃO CORRIGIDA)

session_start();
require_once "db_config.php"; 
header('Content-Type: application/json');

// 1. Bloco de segurança (inalterado)
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['cargo']) || $_SESSION['cargo'] != 1) {
    echo json_encode(['status' => 'error', 'message' => 'Acesso não autorizado.', 'participantes' => []]);
    exit;
}

// $admin_id = $_SESSION['usuario_id']; // <-- NÃO É MAIS NECESSÁRIO
$participantes = [];

// 2. Query SQL CORRIGIDA
// Removemos o "WHERE s.usuario_id = $1" para buscar TODOS os participantes
$sql = "SELECT DISTINCT c.nome_completo 
        FROM clientes c
        JOIN sorteio s ON c.id = s.cliente_id";

// 3. Preparação e execução da consulta com funções pg_*
// Damos um novo nome único para a query preparada
$stmt = pg_prepare($link, "get_todos_participantes_query", $sql); 

if ($stmt) {
    // Executa a query sem parâmetros, pois removemos o $admin_id
    $result = pg_execute($link, "get_todos_participantes_query", []);

    if ($result) {
        while ($row = pg_fetch_assoc($result)) {
            $participantes[] = $row['nome_completo'];
        }
        
        echo json_encode(['status' => 'success', 'participantes' => $participantes]);

    } else {
        echo json_encode(['status' => 'error', 'message' => 'Erro ao executar a consulta.', 'participantes' => []]);
    }

} else {
    echo json_encode(['status' => 'error', 'message' => 'Erro na preparação da consulta ao banco de dados.', 'participantes' => []]);
}

pg_close($link);
?>