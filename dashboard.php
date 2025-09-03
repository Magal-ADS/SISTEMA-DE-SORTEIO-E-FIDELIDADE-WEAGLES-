<?php
// /dashboard.php

// 1. BLOCO DE SEGURANÇA ATUALIZADO
// Ele deve ser a primeira coisa no arquivo.
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
// Incluímos o header depois de toda a lógica PHP
include 'templates/header.php'; 
?>

<title>Dashboard - Resumo da Loja</title>

<div class="main-content" style="flex-direction: column; align-items: stretch; max-width: 1200px; margin: 0 auto;">
    <div class="page-header" style="text-align: left;">
        <h1>Painel do Administrador</h1>
        <p>Resumo das atividades recentes da sua loja.</p>
    </div>

    <div class="dashboard-container">
        <div class="stat-card">
            <h2 class="stat-label">Novos Clientes (Últimos 7 dias)</h2>
            <p class="stat-value"><?php echo $novos_clientes; ?></p>
        </div>

        <div class="stat-card">
            <h2 class="stat-label">Valor em Vendas (Últimos 7 dias)</h2>
            <p class="stat-value"><?php echo $total_vendas_formatado; ?></p>
        </div>
    </div>
</div>

<?php include 'templates/footer.php'; ?>