<?php
// INSTRUÇÃO PARA ESCONDER O HEADER NESTA PÁGINA
$show_header = false;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Se o usuário já estiver logado, redireciona para a página principal
if (isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit();
}
// A linha abaixo incluirá um header.php que não está carregando o CSS, mas a deixamos aqui.
include 'templates/header.php'; 
?>

<title>Acessar o Sistema</title>

<style>
    /* Variáveis de cor para consistência */
    :root {
        --cor-fundo: #FFF5E1;
        --cor-rosa: #F87575;
        --cor-branco: #FFFFFF;
        font-family: 'Poppins', sans-serif;
    }

    /* Estilo para garantir que o fundo da página não fique branco */
    body {
        background-color: var(--cor-fundo);
    }
    
    /* O SEU CÓDIGO CSS PARA O BOTÃO VOLTAR */
    .btn-voltar-canto {
        position: absolute;
        top: 20px;
        right: 20px;
        
        padding: 10px 20px;
        background-color: var(--cor-rosa);
        color: var(--cor-branco);
        border: none;
        border-radius: 20px;
        text-decoration: none;
        font-weight: 500;
        font-size: 15px;
        transition: all 0.3s ease;
    }

    .btn-voltar-canto:hover {
        background-color: #E04F4F;
        transform: translateY(-1px);
    }
</style>
<a href="index.php" class="btn-voltar-canto">Voltar</a>

<div class="card-container">
    <h1>Login do Administrador</h1>
    <p class="subtitle">Acesse com seu CPF/CNPJ e senha para gerenciar.</p>
    
    <form action="php/processa_login.php" method="POST" style="width: 100%;">
        
        <?php if (isset($_SESSION['login_error'])): ?>
            <p style="color: red; margin-bottom: 15px;"><?php echo $_SESSION['login_error']; unset($_SESSION['login_error']); ?></p>
        <?php endif; ?>

        <div class="form-group">
            <label for="cnpj">CPF/CNPJ</label>
            <input type="text" id="cnpj" name="cnpj" required>
        </div>
        <div class="form-group">
            <label for="senha">Senha</label>
            <input type="password" id="senha" name="senha" required>
        </div>
        <button type="submit" class="btn btn-laranja">Entrar</button>
    </form>
</div>

<?php include 'templates/footer.php'; ?>