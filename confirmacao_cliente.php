<?php
// /confirmacao_cliente.php (VERSÃO COM INTERFACE DE EDIÇÃO)

session_start();
if (!isset($_SESSION['cliente_id'])) {
    header('Location: cpf.php');
    exit();
}
require_once 'php/db_config.php';

$cliente_id = $_SESSION['cliente_id'];
$cliente = null;

$sql = "SELECT nome_completo, cpf, whatsapp, data_nascimento FROM clientes WHERE id = $1";
$stmt = pg_prepare($link, "confirmacao_cliente_query_v3", $sql); // Nova query name para evitar conflito
if ($stmt) {
    $result = pg_execute($link, "confirmacao_cliente_query_v3", [$cliente_id]);
    if ($result && pg_num_rows($result) === 1) {
        $cliente = pg_fetch_assoc($result);
    } else {
        session_destroy();
        header('Location: cpf.php');
        pg_close($link);
        exit();
    }
} else {
    session_destroy();
    header('Location: cpf.php');
    pg_close($link);
    exit();
}
pg_close($link);

include 'templates/header.php';
?>

<title>Confirme seus Dados</title>

<style>
    /* Estilos antigos inalterados */
    .card-container { background-color: #f5f5f5 !important; border: 1px solid #ddd !important; box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1) !important; }
    .card-container h1 { color: var(--cor-dourado) !important; }
    .card-container .subtitle { color: var(--cor-texto-secundario) !important; opacity: 1; }
    .confirmation-card .info-item strong { color: var(--cor-dourado) !important; opacity: 1; }
    .confirmation-card .info-item span { color: var(--cor-texto-principal) !important; opacity: 1; }
    .modal-box { background-color: #2c2c2c; }
    .modal-title, .modal-text, .form-group label { color: var(--cor-branco); }
    .modal-box .form-group input { background-color: rgba(0,0,0,0.2); border-color: rgba(255,255,255,0.2); color: var(--cor-branco); }
    .modal-error { color: #ff8a8a; }
    .modal-actions .btn-light { background-color: #444; color: var(--cor-branco); border: 1px solid #555; }

    /* =================== AJUSTES DE LAYOUT E NOVO ÍCONE =================== */
    .confirmation-card {
        background-color: #e9ecef !important;
        border: 1px solid #dee2e6 !important;
        backdrop-filter: none !important;
        display: flex; /* Usar flexbox para melhor controle */
        flex-wrap: wrap; /* Permite que os itens quebrem para a próxima linha */
        gap: 1.5rem 1rem; /* Espaçamento maior entre linhas e colunas */
        padding: 1.5rem; /* Padding interno para o card de dados */
    }
    
    .info-item {
        position: relative;
        flex: 1 1 45%; /* Cada item ocupa quase metade da largura, permitindo quebra */
        min-width: 150px; /* Garante um tamanho mínimo para evitar esmagamento */
        padding-right: 30px; /* Espaço para o ícone */
        word-break: break-word; /* Quebra palavras longas para evitar overflow */
    }

    .edit-icon {
        position: absolute;
        top: 50%;
        right: 0;
        transform: translateY(-50%);
        cursor: pointer;
        opacity: 0.6;
        transition: opacity 0.3s ease;
        line-height: 1; /* Garante que o ícone não adicione altura extra à linha */
    }
    .edit-icon:hover {
        opacity: 1;
    }
    
    .edit-icon svg {
        width: 18px; /* Tamanho do ícone */
        height: 18px;
        fill: var(--cor-dourado); /* Cor do ícone */
        vertical-align: middle; /* Alinha verticalmente com o texto */
    }
    /* ======================================================================= */
</style>

<div class="card-container">
    <h1>Confirme seus Dados</h1>
    <p class="subtitle">Olá! Por favor, confirme se os dados abaixo estão corretos.</p>
    
    <div class="confirmation-card">
        <div class="info-item">
            <strong>Nome Completo:</strong><span id="display-nome_completo"><?php echo htmlspecialchars($cliente['nome_completo']); ?></span>
            <a class="edit-icon" data-field="nome_completo" data-label="Nome Completo" data-value="<?php echo htmlspecialchars($cliente['nome_completo']); ?>">
                <svg viewBox="0 0 24 24"><path d="M3 17.25V21h3.75L18 9.75l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
            </a>
        </div>
        <div class="info-item">
            <strong>CPF:</strong><span><?php echo htmlspecialchars($cliente['cpf']); ?></span>
            </div>
        <div class="info-item">
            <strong>WhatsApp:</strong><span id="display-whatsapp"><?php echo htmlspecialchars($cliente['whatsapp'] ?? '--'); ?></span>
            <a class="edit-icon" data-field="whatsapp" data-label="WhatsApp" data-value="<?php echo htmlspecialchars($cliente['whatsapp'] ?? ''); ?>">
                <svg viewBox="0 0 24 24"><path d="M3 17.25V21h3.75L18 9.75l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
            </a>
        </div>
        <div class="info-item">
            <strong>Data de Nascimento:</strong>
            <span id="display-data_nascimento"><?php echo !empty($cliente['data_nascimento']) ? date('d/m/Y', strtotime($cliente['data_nascimento'])) : '--'; ?></span>
            <a class="edit-icon" data-field="data_nascimento" data-label="Data de Nascimento" data-value="<?php echo !empty($cliente['data_nascimento']) ? date('d/m/Y', strtotime($cliente['data_nascimento'])) : ''; ?>">
                <svg viewBox="0 0 24 24"><path d="M3 17.25V21h3.75L18 9.75l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
            </a>
        </div>
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

<div class="modal-overlay" id="modal-editar-cliente">
    <div class="modal-box">
        <h2 class="modal-title">Editar Informação</h2>
        <form id="form-editar-cliente">
            <input type="hidden" id="edit-field-name" name="field_name">
            <div class="form-group">
                <label id="edit-label" for="edit-value">Novo Valor</label>
                <input type="text" id="edit-value" name="new_value" required>
            </div>
            <div class="form-group">
                <label for="edit-senha-geral">Senha Geral de Liberação</label>
                <input type="password" id="edit-senha-geral" name="senha_geral" required>
            </div>
            <p id="modal-editar-error-message" class="modal-error"></p>
            <div class="modal-actions">
                <button type="button" class="btn btn-light btn-cancelar-edicao">Cancelar</button>
                <button type="submit" class="btn btn-verde">Salvar Alteração</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Lógica do modal de senha (inalterada)
    const btnAbrirPopup = document.getElementById('btn-abrir-popup');
    const modal = document.getElementById('modal-senha');
    const btnCancelar = document.getElementById('btn-cancelar-senha');
    const btnConfirmarSenha = document.getElementById('btn-confirmar-senha');
    const senhaInput = document.getElementById('senha_geral');
    const modalErrorMessage = document.getElementById('modal-error-message');
    // ... (restante da lógica do modal de senha) ...

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

    // =================== NOVO JAVASCRIPT PARA O MODAL DE EDIÇÃO ===================
    const modalEditar = document.getElementById('modal-editar-cliente');
    const formEditar = document.getElementById('form-editar-cliente');
    const editIcons = document.querySelectorAll('.edit-icon');
    const btnCancelarEdicao = document.querySelector('#modal-editar-cliente .btn-cancelar-edicao');

    const editFieldNameInput = document.getElementById('edit-field-name');
    const editValueInput = document.getElementById('edit-value');
    const editLabel = document.getElementById('edit-label');
    const editSenhaInput = document.getElementById('edit-senha-geral');
    const editErrorMessage = document.getElementById('modal-editar-error-message');

    // Elementos do DOM para atualizar após a edição
    const displayNomeCompleto = document.getElementById('display-nome_completo');
    const displayWhatsApp = document.getElementById('display-whatsapp');
    const displayDataNascimento = document.getElementById('display-data_nascimento');

    const openEditModal = (field, label, value) => {
        editFieldNameInput.value = field;
        editLabel.textContent = `Novo Valor para ${label}`; // Atualiza o label dinamicamente
        editValueInput.value = value;
        
        // Aplica máscaras e configurações específicas para campos
        if (field === 'whatsapp') {
            editValueInput.type = 'tel';
            editValueInput.maxLength = 15; // (XX) XXXXX-XXXX
            editValueInput.removeEventListener('input', aplicarMascaraDataNascimento); // Remove se estiver lá
            editValueInput.addEventListener('input', aplicarMascaraWhatsApp);
        } else if (field === 'data_nascimento') {
            editValueInput.type = 'text'; // Manter como texto para a máscara funcionar
            editValueInput.maxLength = 10; // DD/MM/AAAA
            editValueInput.removeEventListener('input', aplicarMascaraWhatsApp); // Remove se estiver lá
            editValueInput.addEventListener('input', aplicarMascaraDataNascimento);
        } else {
            editValueInput.type = 'text';
            editValueInput.maxLength = 255; // Limite padrão para texto
            editValueInput.removeEventListener('input', aplicarMascaraWhatsApp);
            editValueInput.removeEventListener('input', aplicarMascaraDataNascimento);
        }

        editErrorMessage.textContent = '';
        editSenhaInput.value = ''; // Limpa o campo de senha
        modalEditar.classList.add('visible');
        editValueInput.focus();
    };

    const closeEditModal = () => {
        modalEditar.classList.remove('visible');
    };

    editIcons.forEach(icon => {
        icon.addEventListener('click', function(e) {
            e.preventDefault();
            const field = this.dataset.field;
            const label = this.dataset.label;
            const value = this.dataset.value;
            openEditModal(field, label, value);
        });
    });

    if (btnCancelarEdicao) btnCancelarEdicao.addEventListener('click', closeEditModal);
    if (modalEditar) modalEditar.addEventListener('click', e => { if (e.target === modalEditar) closeEditModal(); });

    // Lógica para Salvar Alteração (Fetch) - será implementada no próximo passo
    if (formEditar) {
        formEditar.addEventListener('submit', function(e) {
            e.preventDefault();
            const btnSalvar = formEditar.querySelector('button[type="submit"]');
            btnSalvar.disabled = true;
            btnSalvar.textContent = 'Salvando...';
            editErrorMessage.textContent = '';

            const formData = new FormData(formEditar);
            formData.append('cliente_id', '<?php echo $cliente_id; ?>'); // Adiciona o ID do cliente

            fetch('php/atualizar_cliente.php', { // Novo arquivo PHP para a lógica
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Atualiza o texto na tela
                    if (data.field_name === 'nome_completo') {
                        displayNomeCompleto.textContent = data.new_value_formatted;
                    } else if (data.field_name === 'whatsapp') {
                        displayWhatsApp.textContent = data.new_value_formatted;
                    } else if (data.field_name === 'data_nascimento') {
                        displayDataNascimento.textContent = data.new_value_formatted;
                        // Atualiza o data-value dos ícones de edição para o novo valor
                        document.querySelector('.edit-icon[data-field="data_nascimento"]').dataset.value = data.new_value_raw; 
                    }
                    closeEditModal();
                } else {
                    editErrorMessage.textContent = data.message;
                }
            })
            .catch(error => {
                console.error('Erro na requisição:', error);
                editErrorMessage.textContent = 'Erro de conexão.';
            })
            .finally(() => {
                btnSalvar.disabled = false;
                btnSalvar.textContent = 'Salvar Alteração';
            });
        });
    }

    // Funções de Máscara (transferidas e adaptadas para reutilização)
    function aplicarMascaraWhatsApp(e) {
        let value = e.target.value.replace(/\D/g, '').substring(0, 11);
        value = value.replace(/^(\d{2})(\d)/g, '($1) $2');
        value = value.replace(/(\d{5})(\d)/, '$1-$2');
        e.target.value = value;
    }

    function aplicarMascaraDataNascimento(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length > 2) value = value.substring(0, 2) + '/' + value.substring(2);
        if (value.length > 5) value = value.substring(0, 5) + '/' + value.substring(5, 9);
        e.target.value = value;
    }
    // ==============================================================================
});
</script>

<?php include 'templates/footer.php'; ?>