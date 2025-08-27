<?php 
// Incluímos o header. Como o usuário está logado para ver esta página,
// o header de administrador será exibido automaticamente.
include 'templates/header.php'; 
?>

<title>Dashboard - Resumo da Loja</title>

<div class="dashboard-container">
    <div class="stat-card">
        <h2 class="stat-label">Novos Clientes (Últimos 7 dias)</h2>
        <p class="stat-value" id="novos-clientes-valor">...</p>
    </div>

    <div class="stat-card">
        <h2 class="stat-label">Valor em Vendas (Últimos 7 dias)</h2>
        <p class="stat-value" id="vendas-valor">...</p>
    </div>

    </div>

<?php include 'templates/footer.php'; ?>