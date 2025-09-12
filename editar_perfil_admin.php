<?php
// /editar_perfil_admin.php

// 1. BLOCO DE SEGURANÇA
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['cargo']) || $_SESSION['cargo'] != 1) {
    header("Location: login.php");
    exit();
}

require_once 'php/db_config.php';

// 2. BUSCA OS DADOS ATUAIS DO ADMIN LOGADO
$admin_id = $_SESSION['usuario_id'];
$admin = null;

$stmt = $link->prepare("SELECT nome, cnpj, cpf FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 1) {
    $admin = $result->fetch_assoc();
} else {
    // Se não encontrar o admin, algo está errado. Força o logout por segurança.
    header("Location: logout.php");
    exit();
}
$stmt->close();
$link->close();

include 'templates/header.php';
?>

<title>Editar Perfil</title>

<style>
    /* Estilos para o tema escuro */
    .page-header h1 { color: var(--cor-dourado) !important; }
    .page-header p { color: var(--cor-branco) !important; opacity: 0.8; }

    /* Adapta o formulário (efeito vidro) */
    .settings-form {
        background-color: rgba(44, 44, 44, 0.5) !important;
        backdrop-filter: blur(10px) !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
    }
    .settings-form h2, .settings-form label {
        color: var(--cor-branco) !important;
    }
    .form-group input {
        background-color: rgba(0,0,0,0.2) !important;
        border-color: rgba(255,255,255,0.2) !important;
        color: var(--cor-branco) !important;
    }
</style>

<div class="page-container">
    <header class="page-header">
        <h1>Editar Perfil</h1>
        <p>Atualize suas informações de administrador.</p>
    </header>

    <form id="form-edit-admin" action="php/salvar_perfil_admin.php" method="POST" class="settings-form">
        <h2>Meus Dados</h2>
        
        <input type="hidden" name="id" value="<?php echo $admin_id; ?>">

        <div class="form-group">
            <label for="nome">Nome da Empresa / Administrador</label>
            <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($admin['nome']); ?>" required>
        </div>

        <div class="form-group">
            <label for="cnpj">CNPJ</label>
            <input type="text" id="cnpj" name="cnpj" value="<?php echo htmlspecialchars($admin['cnpj']); ?>" required>
        </div>

        <div class="form-group">
            <label for="senha">Nova Senha (Opcional)</label>
            <input type="password" id="senha" name="senha" placeholder="Deixe em branco para não alterar">
        </div>
        
        <div class="form-group">
            <label for="senha_atual">Senha Atual (Obrigatória para salvar)</label>
            <input type="password" id="senha_atual" name="senha_atual" required>
        </div>

        <button type="submit" class="btn btn-verde">Salvar Alterações</button>
        <p id="form-success-message" class="success-message" style="color: var(--cor-dourado);"></p>
        <p id="form-error-message" class="modal-error" style="color: #ff8a8a;"></p>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('form-edit-admin');
    const successMessage = document.getElementById('form-success-message');
    const errorMessage = document.getElementById('form-error-message');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const button = form.querySelector('button[type="submit"]');
            button.disabled = true;
            button.textContent = 'Salvando...';
            successMessage.textContent = '';
            errorMessage.textContent = '';

            const formData = new FormData(form);

            fetch(form.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    successMessage.textContent = data.message;
                    // Limpa a mensagem após 3 segundos
                    setTimeout(() => { successMessage.textContent = ''; }, 3000);
                    // Limpa os campos de senha por segurança
                    document.getElementById('senha').value = '';
                    document.getElementById('senha_atual').value = '';
                } else {
                    errorMessage.textContent = data.message;
                }
            })
            .catch(error => {
                errorMessage.textContent = 'Erro de conexão. Tente novamente.';
            })
            .finally(() => {
                button.disabled = false;
                button.textContent = 'Salvar Alterações';
            });
        });
    }
});
</script>

<?php include 'templates/footer.php'; ?>