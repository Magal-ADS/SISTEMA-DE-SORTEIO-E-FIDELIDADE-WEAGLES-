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

    <?php if (isset($_SESSION['usuario_id'])): // SE O USUÁRIO ESTIVER LOGADO... ?>

        <?php if (isset($_SESSION['usuario_cargo']) && $_SESSION['usuario_cargo'] == 1): // E SE FOR O ADMIN (CARGO = 1)... ?>
            
            <header class="main-header">
                <div class="header-container">
                    <div class="header-brand">
                        <a href="dashboard.php">
                            <span class="header-shop-icon">&#128722;</span>
                            <span class="header-title"><?php echo htmlspecialchars($_SESSION['usuario_nome']); ?></span>
                        </a>
                    </div>
                    
                    <nav class="header-nav">
                        <a href="base_clientes.php">Base de Clientes</a>
                        <a href="sorteio.php">Sorteio</a>
                        <a href="gerenciamento.php">Gerenciamento</a>
                        <a href="#">Configurações</a>
                    </nav>

                    <div class="header-actions">
                        <a href="logout.php" class="btn-logout">Sair</a>
                        <button class="hamburger-menu" id="hamburger-menu">
                            <span class="bar"></span><span class="bar"></span><span class="bar"></span>
                        </button>
                    </div>
                </div>
            </header>

            <nav class="side-nav" id="side-nav">
                <ul>
                    <li><a href="dashboard.php">Início</a></li>
                    <li><a href="base_clientes.php">Base de Clientes</a></li>
                    <li><a href="sorteio.php">Sorteio</a></li>
                    <li><a href="gerenciamento.php">Gerenciamento</a></li>
                    <li><a href="#">Configurações</a></li>
                    <li><a href="logout.php">Sair</a></li>
                </ul>
            </nav>

        <?php else: // SE FOR OUTRO TIPO DE USUÁRIO LOGADO (EX: VENDEDOR) ?>

            <header class="main-header">
                <div class="header-container">
                    <div class="header-brand">
                        <a href="dashboard.php">
                            <span class="header-shop-icon">&#128100;</span>
                            <span class="header-title">Vendedor: <?php echo htmlspecialchars($_SESSION['usuario_nome']); ?></span>
                        </a>
                    </div>
                    <nav class="header-nav">
                        <a href="cpf.php">Registrar Nova Compra</a>
                    </nav>
                    <div class="header-actions">
                        <a href="logout.php" class="btn-logout">Sair</a>
                    </div>
                </div>
            </header>
            
        <?php endif; ?>

    <?php else: // SE NÃO ESTIVER LOGADO... (Visitante) ?>

        <header class="main-header">
            <div class="header-container">
                <div class="header-brand">
                    <a href="index.php">
                        <span class="header-shop-icon">&#127873;</span>
                        <span class="header-title">Junte Pontos e troque por prêmios</span>
                    </a>
                </div>
                <div class="header-actions">
                    <a href="login.php" class="btn-login-header">Área do Empresário</a>
                </div>
            </div>
        </header>

    <?php endif; ?>

<?php endif; ?>

<main class="main-content">