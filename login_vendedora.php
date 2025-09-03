<?php
// Define que esta página não deve mostrar o header principal
$show_header = false;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirecionamento inteligente se alguém já logado tentar acessar a página
if (isset($_SESSION['usuario_id'])) {
    if (isset($_SESSION['cargo']) && $_SESSION['cargo'] == 1) { // Admin
        header('Location: dashboard.php');
    } else { // Vendedor(a) ou outro
        header('Location: dashboard_vendedor.php');
    }
    exit();
}

include 'templates/header.php'; 
?>

<title>Acesso da Vendedora</title>

<a href="index.php" class="btn-voltar-canto">Voltar</a>

<div class="main-content">
    <div class="card-container">
        <h1>Login da Vendedora</h1>
        <p class="subtitle">Acesse com seu CPF e senha para continuar.</p>
        
        <form action="php/processa_login_vendedor.php" method="POST" style="width: 100%;">
            
            <?php if (isset($_SESSION['login_error'])): ?>
                <p style="color: red; margin-bottom: 15px;"><?php echo $_SESSION['login_error']; unset($_SESSION['login_error']); ?></p>
            <?php endif; ?>

            <div class="form-group">
                <label for="cpf">CPF</label>
                <input type="text" id="cpf" name="cpf" required>
            </div>
            <div class="form-group">
                <label for="senha">Senha</label>
                <input type="password" id="senha" name="senha" required>
            </div>
            <button type="submit" class="btn btn-laranja">Entrar</button>
        </form>
    </div>
</div>

<?php include 'templates/footer.php'; ?>