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
                <input type="text" id="cpf" name="cpf" required 
                       inputmode="numeric" 
                       maxlength="14" placeholder="000.000.000-00">
            </div>
            <div class="form-group">
                <label for="senha">Senha</label>
                <input type="password" id="senha" name="senha" required>
            </div>
            <button type="submit" class="btn btn-laranja">Entrar</button>
        </form>
    </div>
</div>

<script>
document.getElementById('cpf').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, ''); // Remove tudo que não é dígito

    // Aplica a máscara XXX.XXX.XXX-XX
    if (value.length > 3) {
        value = value.substring(0, 3) + '.' + value.substring(3);
    }
    if (value.length > 7) {
        value = value.substring(0, 7) + '.' + value.substring(7);
    }
    if (value.length > 11) {
        value = value.substring(0, 11) + '-' + value.substring(11, 13);
    }

    e.target.value = value;
});
</script>
<?php include 'templates/footer.php'; ?>