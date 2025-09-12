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

            <?php // ==================== MENU DO CHEFE (ADMIN) ==================== ?>
            <?php if (isset($_SESSION['cargo']) && $_SESSION['cargo'] == 1): ?>
                
                <div class="header-brand">
                    <div class="user-menu-container">
                        <button type="button" class="user-menu-button" id="user-menu-button">
                            <span class="header-shop-icon">👑</span>
                            <span class="header-title"><?php echo htmlspecialchars($_SESSION['usuario_nome']); ?></span>
                            <span class="dropdown-icon">&#9662;</span>
                        </button>
                        <div class="user-dropdown-menu" id="user-dropdown-menu">
                            <a href="editar_perfil_admin.php">Editar Perfil</a>
                            <a href="logout.php">Sair</a>
                        </div>
                    </div>
                </div>
                
                <nav class="header-nav">
                    <a href="dashboard.php">Início</a>
                    <a href="base_clientes.php">Base de Clientes</a>
                    <a href="sorteio.php">Sorteio</a>
                    <a href="gerenciamento.php">Gerenciamento</a>
                </nav>
            
                <div class="header-actions">
                    <button class="hamburger-menu" id="hamburger-menu">
                        <span class="bar"></span><span class="bar"></span><span class="bar"></span>
                    </button>
                </div>

            <?php // ==================== MENU DA VENDEDORA (ATUALIZADO) ==================== ?>
            <?php elseif (isset($_SESSION['cargo']) && $_SESSION['cargo'] == 2): ?>

                <div class="header-brand">
                    <a href="dashboard_vendedora.php" style="text-decoration:none;">
                         <span class="header-title">🛍️ Vendedora: <?php echo htmlspecialchars($_SESSION['usuario_nome']); ?></span>
                    </a>
                </div>
                <nav class="header-nav">
                    <a href="dashboard_vendedora.php">Início</a>
                    <a href="base_clientes_vendedor.php">Base de Clientes</a>
                    <?php /* Link de 'Mensagens Programadas' removido */ ?>
                </nav>
                <div class="header-actions">
                    <a href="logout.php" class="btn-logout">Sair</a>
                </div>
            
            <?php // ==================== MENU DE VISITANTE ==================== ?>
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

    <?php // O MENU LATERAL SÓ EXISTE PARA O ADMIN (e já tinha o link de Início) ?>
    <?php if (isset($_SESSION['cargo']) && $_SESSION['cargo'] == 1): ?>
        <nav class="side-nav" id="side-nav">
            <ul>
                <li><a href="dashboard.php">Início</a></li>
                <li><a href="base_clientes.php">Base de Clientes</a></li>
                <li><a href="sorteio.php">Sorteio</a></li>
                <li><a href="gerenciamento.php">Gerenciamento</a></li>
                <li><a href="logout.php">Sair</a></li>
            </ul>
        </nav>
    <?php endif; ?>

<?php endif; ?>

<main class="main-content">