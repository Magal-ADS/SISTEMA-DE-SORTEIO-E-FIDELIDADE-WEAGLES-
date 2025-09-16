<?php
// /dashboard_vendedora.php (VERSÃO CORRIGIDA PARA POSTGRESQL)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['cargo']) || $_SESSION['cargo'] != 2) {
    session_unset();
    session_destroy();
    header("Location: login_vendedora.php");
    exit();
}

require_once 'php/db_config.php';

// =================== INÍCIO DO BLOCO CORRIGIDO ===================
$vendedora_id = $_SESSION['usuario_id'];

// --- Contar CLIENTES ÚNICOS que compraram com a vendedora nos últimos 7 dias ---
// MUDANÇA: DATE_SUB trocado por sintaxe de intervalo e consulta convertida para pg_*
$sql_clientes = "SELECT COUNT(DISTINCT cliente_id) as total_clientes FROM compras WHERE vendedor_id = $1 AND data_compra >= NOW() - interval '7 day'";
$stmt_clientes = pg_prepare($link, "vendedora_dashboard_clientes", $sql_clientes);
$resultado_clientes = pg_execute($link, "vendedora_dashboard_clientes", [$vendedora_id]);
$clientes_atendidos = pg_fetch_assoc($resultado_clientes)['total_clientes'] ?? 0;

// --- Somar o valor das vendas da vendedora nos últimos 7 dias ---
// MUDANÇA: Mesma alteração na função de data e conversão da consulta
$sql_vendas_valor = "SELECT SUM(valor) as total_valor FROM compras WHERE vendedor_id = $1 AND data_compra >= NOW() - interval '7 day'";
$stmt_vendas_valor = pg_prepare($link, "vendedora_dashboard_vendas", $sql_vendas_valor);
$resultado_vendas_valor = pg_execute($link, "vendedora_dashboard_vendas", [$vendedora_id]);
$total_vendas_valor = pg_fetch_assoc($resultado_vendas_valor)['total_valor'] ?? 0;
$total_vendas_formatado = "R$ " . number_format($total_vendas_valor, 2, ',', '.');

pg_close($link);
// ==================== FIM DO BLOCO CORRIGIDO =====================

include 'templates/header.php'; 
?>

<title>Dashboard da Vendedora</title>

<style>
    /* Estilos para o tema escuro e "MUITO LINDO" do Dashboard */
    .page-header h1 {
        color: var(--cor-dourado) !important;
    }
    .page-header p {
        color: var(--cor-branco) !important;
        opacity: 0.8;
    }
    /* Estilo "Vidro" para os cards de estatística */
    .stat-card {
        background-color: rgba(44, 44, 44, 0.6) !important;
        backdrop-filter: blur(10px) !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        color: var(--cor-branco) !important;
    }
    .stat-label {
        color: rgba(255, 255, 255, 0.7) !important;
        font-size: 1rem !important;
    }
    .stat-value {
        color: var(--cor-dourado) !important; /* Valor principal em dourado */
        font-size: 3rem !important;
    }
    /* Estilo para os ícones que adicionamos */
    .stat-card-icon {
        font-size: 2.5rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }
</style>

<div class="page-container">
    <header class="page-header">
        <h1>Painel da Vendedora</h1>
        <p>Resumo do seu desempenho nos últimos 7 dias.</p>
    </header>

    <div class="dashboard-container">
        <div class="stat-card">
            <div class="stat-card-icon">👥</div>
            <h2 class="stat-label">Clientes Atendidos (Últimos 7 dias)</h2>
            <p class="stat-value"><?php echo $clientes_atendidos; ?></p>
        </div>

        <div class="stat-card">
            <div class="stat-card-icon">💰</div>
            <h2 class="stat-label">Seu Valor em Vendas (Últimos 7 dias)</h2>
            <p class="stat-value"><?php echo $total_vendas_formatado; ?></p>
        </div>
    </div>
</div>

<?php include 'templates/footer.php'; ?>