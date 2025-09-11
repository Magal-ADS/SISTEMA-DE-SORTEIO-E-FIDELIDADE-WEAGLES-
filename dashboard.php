<?php
// /dashboard.php

// 1. BLOCO DE SEGURANÇA ATUALIZADO
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Verifica se o usuário está logado E se o cargo é de Administrador (1)
if (!isset($_SESSION['cargo']) || $_SESSION['cargo'] != 1) {
    header("Location: login.php");
    exit();
}

// 2. LÓGICA PARA BUSCAR OS DADOS DO DASHBOARD
require_once 'php/db_config.php';

// --- Contar novos clientes (da loja toda) nos últimos 7 dias ---
$sql_clientes = "SELECT COUNT(id) as total_novos_clientes FROM clientes WHERE data_cadastro >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
$resultado_clientes = $link->query($sql_clientes);
$novos_clientes = $resultado_clientes->fetch_assoc()['total_novos_clientes'] ?? 0;

// --- Somar o valor de TODAS as vendas nos últimos 7 dias ---
$sql_vendas = "SELECT SUM(valor) as total_vendas FROM compras WHERE data_compra >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
$resultado_vendas = $link->query($sql_vendas);
$total_vendas = $resultado_vendas->fetch_assoc()['total_vendas'] ?? 0;
// Formata o valor para a moeda brasileira
$total_vendas_formatado = "R$ " . number_format($total_vendas, 2, ',', '.');


// 3. INCLUSÃO DO HEADER
include 'templates/header.php'; 
?>

<title>Dashboard - Resumo da Loja</title>

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
        <h1>Painel do Administrador</h1>
        <p>Resumo das atividades recentes da sua loja.</p>
    </header>

    <div class="dashboard-container">
        <div class="stat-card">
            <div class="stat-card-icon">👥</div>
            <h2 class="stat-label">Novos Clientes (Últimos 7 dias)</h2>
            <p class="stat-value"><?php echo $novos_clientes; ?></p>
        </div>

        <div class="stat-card">
            <div class="stat-card-icon">💰</div>
            <h2 class="stat-label">Valor em Vendas (Últimos 7 dias)</h2>
            <p class="stat-value"><?php echo $total_vendas_formatado; ?></p>
        </div>
    </div>
</div>

<?php include 'templates/footer.php'; ?>