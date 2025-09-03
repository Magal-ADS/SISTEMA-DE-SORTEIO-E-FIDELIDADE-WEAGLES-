<?php
// /gerenciamento.php

// BLOCO DE SEGURANÇA ATUALIZADO
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Segurança: Apenas o Admin (CARGO = 1) pode acessar esta página.
if (!isset($_SESSION['cargo']) || $_SESSION['cargo'] != 1) {
    header("Location: login.php");
    exit();
}

// O resto do seu código continua igual.
include 'templates/header.php';
?>

<title>Gerenciamento</title>

<div class="page-container">
    <header class="page-header">
        <h1>Painel de Gerenciamento</h1>
        <p>Acesse as principais áreas administrativas do sistema.</p>
    </header>

    <div class="management-grid">

        <a href="gerenciar_funcionarios.php" class="mgmt-card">
            <div class="mgmt-card-icon">👥</div>
            <h3>Gerenciar Funcionários</h3>
            <p>Adicione, remova ou edite os dados dos vendedores do sistema.</p>
        </a>

        <a href="gerenciar_filtros.php" class="mgmt-card">
            <div class="mgmt-card-icon">📊</div>
            <h3>Gerenciar Filtros</h3>
            <p>Personalize os filtros da base de clientes e crie novos segmentos.</p>
        </a>

        <a href="gerenciar_sorteio.php" class="mgmt-card">
            <div class="mgmt-card-icon">🏆</div>
            <h3>Gerenciar Sorteio</h3>
            <p>Limpe a urna de sorteio, visualize ganhadores anteriores e defina regras.</p>
        </a>

        <a href="gerenciar_tela_inicial.php" class="mgmt-card">
            <div class="mgmt-card-icon">🖥️</div>
            <h3>Gerenciar Tela Inicial</h3>
            <p>Altere os textos e imagens da página de participação dos clientes.</p>
        </a>

    </div>
</div>

<?php
// Inclui o rodapé
include 'templates/footer.php';
?>