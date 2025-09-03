<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">
    <title>Sistema de Sorteio</title>
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php 
if (!isset($show_header) || $show_header !== false): 
?>
    <header class="main-header">
        <div class="header-container">

            <?php // Menu do Administrador Logado ?>
            <?php if (isset($_SESSION['cargo']) && $_SESSION['cargo'] == 1): ?>
                
                <div class="header-brand">
                    <a href="dashboard.php">
                        <span class="header-title">👑 <?php echo htmlspecialchars($_SESSION['usuario_nome']); ?></span>
                    </a>
                </div>
                <nav class="header-nav">
                    <a href="base_clientes.php">Base de Clientes</a>
                    <a href="sorteio.php">Sorteio</a>
                    <a href="gerenciamento.php">Gerenciamento</a>
                </nav>
                <div class="header-actions">
                    <a href="logout.php" class="btn-logout">Sair</a>
                </div>

            <?php // Menu da Vendedora Logada ?>
            <?php elseif (isset($_SESSION['cargo']) && $_SESSION['cargo'] == 2): ?>

                <div class="header-brand">
                    <a href="dashboard_vendedora.php" style="text-decoration:none;">
                         <span class="header-title">🛍️ Vendedora: <?php echo htmlspecialchars($_SESSION['usuario_nome']); ?></span>
                    </a>
                </div>
                <nav class="header-nav">
                    <a href="base_clientes.php">Base de Clientes</a>
                    <a href="mensagens_programadas.php">Mensagens Programadas</a>
                </nav>
                <div class="header-actions">
                    <a href="logout.php" class="btn-logout">Sair</a>
                </div>
            
            <?php // Menu de Visitante (Não Logado) com os links CORRETOS ?>
            <?php else: ?>

                <div class="header-brand">
                    <a href="index.php">
                        <span class="header-shop-icon">🎁</span>
                        <span class="header-title">Junte Pontos e troque por prêmios</span>
                    </a>
                </div>
                <div class="header-actions">
                    <a href="login_vendedora.php" class="btn-login-header">Área da Vendedora</a>
                    <a href="login.php" class="btn-login-header">Área do Empresário</a>
                </div>

            <?php endif; ?>
        </div>
    </header>

<?php endif; ?>

<main class="main-content">