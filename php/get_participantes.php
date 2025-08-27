<?php
// /php/get_participantes.php

session_start();
require_once "db_config.php";
header('Content-Type: application/json');

// Apenas o Admin (CARGO = 1) pode acessar esta lista.
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['usuario_cargo']) || $_SESSION['usuario_cargo'] != 1) {
    echo json_encode(['status' => 'error', 'message' => 'Acesso não autorizado.']);
    exit;
}

$admin_id = $_SESSION['usuario_id'];
$participantes = [];

// Busca o nome de cada cliente distinto que está na tabela de sorteio
$sql = "SELECT DISTINCT c.nome_completo 
        FROM clientes c
        JOIN sorteio s ON c.id = s.cliente_id
        WHERE c.usuario_id = ?";

$stmt = $link->prepare($sql);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();

while($row = $result->fetch_assoc()){
    // Adiciona apenas a string do nome a um array
    $participantes[] = $row['nome_completo'];
}

echo json_encode(['status' => 'success', 'participantes' => $participantes]);

$stmt->close();
$link->close();
?>