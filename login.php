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
    /* Variáveis de cor para consistência com a nova paleta */
    :root {
        --cor-fundo: #f0f0f0;        /* Cinza bem claro */
        --cor-dourado: #D4AF37;        /* Dourado */
        --cor-branco: #FFFFFF;        /* Branco */
        font-family: 'Poppins', sans-serif;
    }

    /* Estilo para garantir que o fundo da página não fique branco */
    body {
        background-color: var(--cor-fundo);
    }
    
    /* O SEU CÓDIGO CSS PARA O BOTÃO VOLTAR ATUALIZADO */
    .btn-voltar-canto {
        position: absolute;
        top: 20px;
        right: 20px;
        
        padding: 10px 20px;
        background-color: var(--cor-dourado); /* <-- COR ATUALIZADA */
        color: var(--cor-branco);
        border: none;
        border-radius: 20px;
        text-decoration: none;
        font-weight: 500;
        font-size: 15px;
        transition: all 0.3s ease;
    }

    .btn-voltar-canto:hover {
        background-color: #a8874a; /* <-- COR HOVER ATUALIZADA (dourado escuro) */
        transform: translateY(-1px);
    }
</style>
<a href="index.php" class="btn-voltar-canto">Voltar</a>

<div class="card-container">
    <h1>Login do Administrador</h1>
    <p class="subtitle">Acesse com seu CNPJ e senha para gerenciar.</p>
    
    <form action="php/processa_login.php" method="POST" style="width: 100%;">
        
        <?php if (isset($_SESSION['login_error'])): ?>
            <p style="color: red; margin-bottom: 15px;"><?php echo $_SESSION['login_error']; unset($_SESSION['login_error']); ?></p>
        <?php endif; ?>

        <div class="form-group">
            <label for="cnpj">CNPJ</label>
            <input type="text" id="cnpj" name="cnpj" required 
                   inputmode="numeric" pattern="[0-9]*"
                   maxlength="18" placeholder="00.000.000/0000-00">
        </div>
        <div class="form-group">
            <label for="senha">Senha</label>
            <input type="password" id="senha" name="senha" required>
        </div>
        <button type="submit" class="btn btn-laranja">Entrar</button>
    </form>
</div>

<script>
document.getElementById('cnpj').addEventListener('input', function (e) {
    let value = e.target.value.replace(/\D/g, ''); // Remove tudo que não é dígito
    
    // Aplica a máscara XX.XXX.XXX/XXXX-XX
    if (value.length > 2) {
        value = value.substring(0, 2) + '.' + value.substring(2);
    }
    if (value.length > 6) {
        value = value.substring(0, 6) + '.' + value.substring(6);
    }
    if (value.length > 10) {
        value = value.substring(0, 10) + '/' + value.substring(10);
    }
    if (value.length > 15) {
        value = value.substring(0, 15) + '-' + value.substring(15, 17);
    }

    e.target.value = value;
});
</script>
<?php include 'templates/footer.php'; ?>