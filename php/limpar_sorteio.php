<?php
// /php/limpar_sorteio.php (Versão PostgreSQL)

session_start();
require_once "db_config.php"; // Conexão PostgreSQL
header('Content-Type: application/json');

// Segurança: Apenas o Admin pode limpar a urna.
// AJUSTE DE CONSISTÊNCIA: Usando a variável de sessão 'cargo' padronizada.
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['cargo']) || $_SESSION['cargo'] != 1) {
    echo json_encode(['status' => 'error', 'message' => 'Acesso não autorizado.']);
    exit;
}

// O comando TRUNCATE é SQL padrão e funciona perfeitamente no PostgreSQL.
// Adicionamos "RESTART IDENTITY" que é a forma explícita no Postgres para reiniciar o contador do ID.
$sql = "TRUNCATE TABLE sorteio RESTART IDENTITY";

// Executa a query diretamente com pg_query, que é o equivalente ao $link->query()
$result = pg_query($link, $sql);

if ($result) {
    echo json_encode(['status' => 'success', 'message' => 'A urna de sorteio foi limpa com sucesso!']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Erro ao tentar limpar a urna.']);
}

// Fecha a conexão com o PostgreSQL
pg_close($link);
?>