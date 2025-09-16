<?php
// /gerenciar_tela_inicial.php (VERSÃO CORRIGIDA PARA POSTGRESQL)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['cargo']) || $_SESSION['cargo'] != 1) {
    header("Location: login.php");
    exit();
}

require_once 'php/db_config.php';

// =================== INÍCIO DO BLOCO CORRIGIDO ===================
// A consulta SQL é compatível
$sql = "SELECT * FROM configuracoes WHERE chave = 'tela_inicial_info_card_texto'";

// A execução foi trocada para as funções pg_*
$result = pg_query($link, $sql);
$config = pg_fetch_assoc($result);

pg_close($link);
// ==================== FIM DO BLOCO CORRIGIDO =====================

include 'templates/header.php';
?>

<title>Gerenciar Tela Inicial</title>

<style>
    /* Estilos para o tema escuro */
    .page-header h1 { color: var(--cor-dourado) !important; }
    .page-header p { color: var(--cor-branco) !important; opacity: 0.8; }

    /* Adapta o formulário e a área de preview (efeito vidro) */
    .settings-form, .preview-area {
        background-color: rgba(44, 44, 44, 0.5) !important;
        backdrop-filter: blur(10px) !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
    }

    /* Adapta os textos e campos do formulário */
    .settings-form h2, .settings-form label, .preview-area h4 {
        color: var(--cor-branco) !important;
    }
    .form-group textarea {
        background-color: rgba(0,0,0,0.2) !important;
        border-color: rgba(255,255,255,0.2) !important;
        color: var(--cor-branco) !important;
    }
</style>

<div class="page-container">
    <header class="page-header">
        <h1>Gerenciar Tela Inicial</h1>
        <p>Edite os textos e outros elementos da página de participação.</p>
    </header>

    <div class="edit-layout">
        <div class="edit-form-column">
            <form id="form-tela-inicial" action="php/salvar_configuracoes.php" method="POST" class="settings-form">
                <h2>Card de Informação</h2>
                <div class="form-group">
                    <label for="tela_inicial_info_card_texto"><?php echo htmlspecialchars($config['descricao']); ?></label>
                    <textarea name="tela_inicial_info_card_texto" id="tela_inicial_info_card_texto" rows="4" required><?php echo htmlspecialchars($config['valor']); ?></textarea>
                </div>
                <button type="submit" class="btn btn-verde">Salvar Alterações</button>
                <p id="form-success-message" class="success-message"></p>
            </form>
        </div>

        <div class="preview-column">
            <div class="preview-area">
                <h4>Pré-visualização em tempo real:</h4>
                <div class="info-card">
                    <span class="info-card-icon">&#127915;</span>
                    <p id="preview-text" class="info-card-text"><?php echo htmlspecialchars($config['valor']); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// O JavaScript não precisa de alteração
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('form-tela-inicial');
    const successMessage = document.getElementById('form-success-message');
    let isSubmitting = false;

    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            if (isSubmitting) return;
            isSubmitting = true;
            const button = form.querySelector('button[type="submit"]');
            button.disabled = true;
            button.textContent = 'Salvando...';
            successMessage.textContent = '';
            fetch(form.action, { method: 'POST', body: new FormData(form) })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    successMessage.textContent = data.message;
                    setTimeout(() => { successMessage.textContent = ''; }, 3000);
                } else {
                    alert('Erro: ' + (data.message || 'Ocorreu um erro desconhecido.'));
                }
            })
            .finally(() => {
                button.disabled = false;
                button.textContent = 'Salvar Alterações';
                isSubmitting = false;
            });
        });
    }

    const textarea = document.getElementById('tela_inicial_info_card_texto');
    const previewText = document.getElementById('preview-text');

    if (textarea && previewText) {
        textarea.addEventListener('input', function() {
            previewText.textContent = textarea.value;
        });
    }
});
</script>

<?php include 'templates/footer.php'; ?>