<?php
// /php/get_participantes.php

session_start();
require_once "db_config.php";
header('Content-Type: application/json');

// 1. CORREÇÃO: Usando a variável de sessão correta -> $_SESSION['cargo']
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['cargo']) || $_SESSION['cargo'] != 1) {
    // Retorna um erro JSON se não for o admin
    echo json_encode(['status' => 'error', 'message' => 'Acesso não autorizado.', 'participantes' => []]);
    exit;
}

$admin_id = $_SESSION['usuario_id'];
$participantes = [];

// 2. CORREÇÃO: A busca agora filtra pelo usuario_id da tabela de sorteio, para consistência.
$sql = "SELECT DISTINCT c.nome_completo 
        FROM clientes c
        JOIN sorteio s ON c.id = s.cliente_id
        WHERE s.usuario_id = ?";

if ($stmt = $link->prepare($sql)) {
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while($row = $result->fetch_assoc()){
        $participantes[] = $row['nome_completo'];
    }
    
    $stmt->close();
    
    echo json_encode(['status' => 'success', 'participantes' => $participantes]);

} else {
    // Retorna um erro JSON se a preparação da query falhar
    echo json_encode(['status' => 'error', 'message' => 'Erro na consulta ao banco de dados.', 'participantes' => []]);
}

$link->close();
?>