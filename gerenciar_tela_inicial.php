<?php
// /gerenciar_tela_inicial.php (Versão com Preview)

session_start();
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['usuario_cargo']) || $_SESSION['usuario_cargo'] != 1) {
    header("Location: login.php");
    exit();
}

require_once 'php/db_config.php';

$config = $link->query("SELECT * FROM configuracoes WHERE chave = 'tela_inicial_info_card_texto'")->fetch_assoc();

$link->close();
include 'templates/header.php';
?>

<title>Gerenciar Tela Inicial</title>

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
document.addEventListener('DOMContentLoaded', function() {
    // --- LÓGICA DE SALVAR O FORMULÁRIO (sem alterações) ---
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

    // --- NOVA LÓGICA PARA A PRÉ-VISUALIZAÇÃO EM TEMPO REAL ---
    const textarea = document.getElementById('tela_inicial_info_card_texto');
    const previewText = document.getElementById('preview-text');

    if (textarea && previewText) {
        // "Escuta" cada vez que uma tecla é pressionada no campo de texto
        textarea.addEventListener('input', function() {
            // Atualiza o texto da pré-visualização com o valor do campo
            previewText.textContent = textarea.value;
        });
    }
});
</script>

<?php include 'templates/footer.php'; ?>