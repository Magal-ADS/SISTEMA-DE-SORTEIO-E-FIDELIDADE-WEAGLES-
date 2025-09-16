<?php
// /confirmacao_cliente.php (VERSÃO CORRIGIDA PARA POSTGRESQL)

session_start();
if (!isset($_SESSION['cliente_id'])) {
    header('Location: cpf.php');
    exit();
}
require_once 'php/db_config.php';

// =================== INÍCIO DO BLOCO CORRIGIDO ===================
$cliente_id = $_SESSION['cliente_id'];
$cliente = null;

// 1. SQL com placeholder do PostgreSQL ($1)
$sql = "SELECT nome_completo, cpf, whatsapp, data_nascimento FROM clientes WHERE id = $1";

// 2. Prepara e executa a consulta com as funções pg_*
$stmt = pg_prepare($link, "confirmacao_cliente_query", $sql);
if ($stmt) {
    $result = pg_execute($link, "confirmacao_cliente_query", [$cliente_id]);

    // 3. Verifica se encontrou o cliente e busca os dados
    if ($result && pg_num_rows($result) === 1) {
        $cliente = pg_fetch_assoc($result);
    } else {
        // Se não encontrou, destrói a sessão e redireciona
        session_destroy();
        header('Location: cpf.php');
        pg_close($link); // Fecha a conexão antes de sair
        exit();
    }
} else {
    // Se a preparação da query falhar, também redireciona
    session_destroy();
    header('Location: cpf.php');
    pg_close($link); // Fecha a conexão antes de sair
    exit();
}

pg_close($link); // Fecha a conexão em caso de sucesso
// ==================== FIM DO BLOCO CORRIGIDO =====================

include 'templates/header.php';
?>

<title>Confirme seus Dados</title>

<style>
    /* Estilos para o card principal prateado/branco */
    .card-container {
        background-color: #f5f5f5 !important;
        border: 1px solid #ddd !important;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1) !important;
    }

    /* Títulos dentro do card principal */
    .card-container h1 {
        color: var(--cor-dourado) !important;
    }
    .card-container .subtitle {
        color: var(--cor-texto-secundario) !important; /* Texto escuro no fundo claro */
        opacity: 1;
    }

    /* Card interno com os dados */
    .confirmation-card {
        background-color: #e9ecef !important; /* Um cinza um pouco mais escuro para destaque */
        border: 1px solid #dee2e6 !important;
        backdrop-filter: none !important; /* Remove o efeito de vidro */
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.25rem !important;
    }
    
    /* Textos dentro do card interno */
    .confirmation-card .info-item strong {
        color: var(--cor-dourado) !important;
        opacity: 1;
    }
    .confirmation-card .info-item span {
        color: var(--cor-texto-principal) !important; /* Texto escuro */
        opacity: 1;
    }
    
    /* Modal de senha (mantém o tema escuro para contraste) */
    .modal-box { background-color: #2c2c2c; }
    .modal-title, .modal-text, .form-group label { color: var(--cor-branco); }
    .modal-box .form-group input { background-color: rgba(0,0,0,0.2); border-color: rgba(255,255,255,0.2); color: var(--cor-branco); }
    .modal-error { color: #ff8a8a; }
    .modal-actions .btn-light { background-color: #444; color: var(--cor-branco); border: 1px solid #555; }
</style>

<div class="card-container">
    <h1>Confirme seus Dados</h1>
    <p class="subtitle">Olá! Por favor, confirme se os dados abaixo estão corretos.</p>
    
    <div class="confirmation-card">
        <div class="info-item"><strong>Nome Completo:</strong><span><?php echo htmlspecialchars($cliente['nome_completo']); ?></span></div>
        <div class="info-item"><strong>CPF:</strong><span><?php echo htmlspecialchars($cliente['cpf']); ?></span></div>
        <div class="info-item"><strong>WhatsApp:</strong><span><?php echo htmlspecialchars($cliente['whatsapp']); ?></span></div>
        <div class="info-item"><strong>Data de Nascimento:</strong><span><?php echo date('d/m/Y', strtotime($cliente['data_nascimento'])); ?></span></div>
    </div>
    
    <p id="form-error-message" style="color: #D8000C; text-align: center; min-height: 20px;"></p>

    <button type="button" id="btn-abrir-popup" class="btn btn-verde">Confirmar e Registrar Compra</button>
</div>

<div class="modal-overlay" id="modal-senha">
    <div class="modal-box">
        <h2 class="modal-title">Senha do Vendedor</h2>
        <p class="modal-text">Digite a senha de liberação para continuar com o registro da compra.</p>
        <div class="form-group">
            <label for="senha_geral">Senha Geral</label>
            <input type="password" id="senha_geral" name="senha_geral" placeholder="Digite a senha aqui" inputmode="numeric">
            <p id="modal-error-message" class="modal-error"></p>
        </div>
        <div class="modal-actions">
            <button type="button" class="btn btn-light" id="btn-cancelar-senha">Cancelar</button>
            <button type="button" class="btn btn-verde" id="btn-confirmar-senha">Liberar</button>
        </div>
    </div>
</div>

<script>
    // Seu JavaScript funcional permanece 100% intacto
document.addEventListener('DOMContentLoaded', function() {
    const btnAbrirPopup = document.getElementById('btn-abrir-popup');
    const modal = document.getElementById('modal-senha');
    const btnCancelar = document.getElementById('btn-cancelar-senha');
    const btnConfirmarSenha = document.getElementById('btn-confirmar-senha');
    const senhaInput = document.getElementById('senha_geral');
    const modalErrorMessage = document.getElementById('modal-error-message');
    if (btnAbrirPopup) {
        btnAbrirPopup.addEventListener('click', function() {
            if(senhaInput) senhaInput.value = '';
            if(modalErrorMessage) modalErrorMessage.textContent = '';
            if (modal) {
                modal.classList.add('visible');
                senhaInput.focus();
            }
        });
    }
    const closeModal = () => {
        if (modal) modal.classList.remove('visible');
    };
    if (btnCancelar) btnCancelar.addEventListener('click', closeModal);
    if (modal) modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });
    if (btnConfirmarSenha) {
        btnConfirmarSenha.addEventListener('click', function() {
            const formData = new FormData();
            formData.append('senha_geral', senhaInput.value);
            btnConfirmarSenha.disabled = true;
            btnConfirmarSenha.textContent = 'Verificando...';
            modalErrorMessage.textContent = '';
            fetch('php/verificar_senha_geral.php', { 
                method: 'POST', 
                body: formData 
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    window.location.href = 'dados_compra.php';
                } else {
                    modalErrorMessage.textContent = data.message;
                    btnConfirmarSenha.disabled = false;
                    btnConfirmarSenha.textContent = 'Liberar';
                }
            })
            .catch(error => {
                console.error('Erro na requisição:', error);
                modalErrorMessage.textContent = 'Erro de conexão.';
                btnConfirmarSenha.disabled = false;
                btnConfirmarSenha.textContent = 'Liberar';
            });
        });
    }
});
</script>

<?php include 'templates/footer.php'; ?>