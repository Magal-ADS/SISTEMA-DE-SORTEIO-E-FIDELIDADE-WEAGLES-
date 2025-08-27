<?php
// /php/limpar_sorteio.php

session_start();
require_once "db_config.php";
header('Content-Type: application/json');

// Segurança: Apenas o Admin pode limpar a urna.
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['usuario_cargo']) || $_SESSION['usuario_cargo'] != 1) {
    echo json_encode(['status' => 'error', 'message' => 'Acesso não autorizado.']);
    exit;
}

// TRUNCATE é mais eficiente que DELETE para limpar uma tabela inteira e reseta o auto-incremento.
$sql = "TRUNCATE TABLE sorteio";

if ($link->query($sql)) {
    echo json_encode(['status' => 'success', 'message' => 'A urna de sorteio foi limpa com sucesso!']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Erro ao tentar limpar a urna.']);
}

$link->close();
?>