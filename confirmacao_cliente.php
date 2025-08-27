<?php
// /confirmacao_cliente.php

session_start();

// Segurança: Se não houver um cliente na sessão, volta para o início.
if (!isset($_SESSION['cliente_id'])) {
    header('Location: cpf.php');
    exit();
}

require_once 'php/db_config.php';

// Busca TODOS os dados do cliente que está na sessão para exibir no card
$cliente_id = $_SESSION['cliente_id'];
$cliente = null;

$stmt = $link->prepare("SELECT nome_completo, cpf, whatsapp, data_nascimento FROM clientes WHERE id = ?");
$stmt->bind_param("i", $cliente_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 1) {
    $cliente = $result->fetch_assoc();
} else {
    // Caso raro onde o ID da sessão não corresponde a um cliente.
    session_destroy();
    header('Location: cpf.php');
    exit();
}
$stmt->close();
$link->close();

include 'templates/header.php';
?>

<title>Confirme seus Dados</title>

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

    <button type="button" id="btn-abrir-popup" class="btn btn-verde">Confirmar</button>
</div>


<div class="modal-overlay" id="modal-senha">
    <div class="modal-box">
        <h2 class="modal-title">Senha do Vendedor</h2>
        <p class="modal-text">Digite a senha de liberação para continuar com o registro da compra.</p>
        <div class="form-group">
            <label for="senha_geral">Senha Geral</label>
            <input type="password" id="senha_geral" name="senha_geral" placeholder="Digite a senha aqui">
            <p id="modal-error-message" class="modal-error"></p>
        </div>
        <div class="modal-actions">
            <button type="button" class="btn btn-light" id="btn-cancelar-senha">Cancelar</button>
            <button type="button" class="btn btn-verde" id="btn-confirmar-senha">Liberar</button>
        </div>
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {
    // Seleção dos Elementos
    const btnAbrirPopup = document.getElementById('btn-abrir-popup');
    const modal = document.getElementById('modal-senha');
    const btnCancelar = document.getElementById('btn-cancelar-senha');
    const btnConfirmarSenha = document.getElementById('btn-confirmar-senha');
    const senhaInput = document.getElementById('senha_geral');
    const modalErrorMessage = document.getElementById('modal-error-message');

    // Lógica para Abrir o Pop-up
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

    // Lógica para Fechar o Pop-up
    const closeModal = () => {
        if (modal) modal.classList.remove('visible');
    };
    if (btnCancelar) btnCancelar.addEventListener('click', closeModal);
    if (modal) modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });

    // Lógica para Confirmar a Senha e Prosseguir
    if (btnConfirmarSenha) {
        btnConfirmarSenha.addEventListener('click', function() {
            const formData = new FormData();
            formData.append('senha_geral', senhaInput.value);

            btnConfirmarSenha.disabled = true;
            btnConfirmarSenha.textContent = 'Verificando...';
            modalErrorMessage.textContent = '';

            // Envia a senha para o script PHP de verificação GERAL
            fetch('php/verificar_senha_geral.php', { 
                method: 'POST', 
                body: formData 
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Se a senha estiver correta, redireciona para a próxima etapa
                    window.location.href = 'dados_compra.php';
                } else {
                    // Se estiver errada, mostra o erro e reativa o botão
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