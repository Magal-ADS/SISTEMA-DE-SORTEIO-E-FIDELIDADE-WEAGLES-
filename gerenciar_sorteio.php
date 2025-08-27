<?php
// /gerenciar_sorteio.php

session_start();
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['usuario_cargo']) || $_SESSION['usuario_cargo'] != 1) {
    header("Location: login.php");
    exit();
}

require_once 'php/db_config.php';

$config_sorteio = $link->query("SELECT * FROM configuracoes WHERE chave = 'sorteio_valor_base_extra'")->fetch_assoc();
$total_cupons = $link->query("SELECT COUNT(*) as total FROM sorteio WHERE usuario_id = {$_SESSION['usuario_id']}")->fetch_assoc()['total'];

$link->close();
include 'templates/header.php';
?>

<title>Gerenciar Sorteio</title>

<div class="page-container">
    <header class="page-header">
        <h1>Gerenciar Sorteio</h1>
        <p>Ajuste as regras e administre a urna de números da sorte.</p>
    </header>

    <form id="form-regra-sorteio" action="php/salvar_configuracoes.php" method="POST" class="settings-form">
        <h2>Regra de Geração de Números</h2>
        <div class="form-group">
            <label for="sorteio_valor_base_extra"><?php echo htmlspecialchars($config_sorteio['descricao']); ?></label>
            <input type="number" step="0.01" name="sorteio_valor_base_extra" id="sorteio_valor_base_extra" value="<?php echo htmlspecialchars($config_sorteio['valor']); ?>" required>
        </div>
        <button type="submit" class="btn btn-verde">Salvar Regra</button>
        <p id="form-success-message" class="success-message"></p>
    </form>

    <div class="danger-zone">
        <h2>Limpar Urna do Sorteio</h2>
        <p>Esta ação apagará **permanentemente** todos os números da sorte existentes. Use isso para iniciar um novo ciclo de sorteio (ex: um novo mês).</p>
        <p>Atualmente existem <strong><?php echo $total_cupons; ?></strong> números na urna.</p>
        <button id="btn-abrir-modal-limpeza" class="btn btn-action delete">Limpar Urna Agora</button>
    </div>
</div>


<div class="modal-overlay" id="modal-confirmar-limpeza">
    <div class="modal-box">
        <h2 class="modal-title">Atenção! Ação Irreversível</h2>
        <p class="modal-text">
            Você tem certeza que deseja limpar a urna? Todos os 
            <strong><?php echo $total_cupons; ?></strong> 
            números da sorte serão **apagados permanentemente**.
        </p>
        <div class="modal-actions">
            <button type="button" class="btn btn-light" id="btn-cancelar-limpeza">Cancelar</button>
            <button type="button" class="btn btn-action delete" id="btn-confirmar-limpeza">Sim, Limpar Urna</button>
        </div>
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- Lógica para salvar a regra do sorteio (sem alterações) ---
    const formRegra = document.getElementById('form-regra-sorteio');
    const successMessage = document.getElementById('form-success-message');
    let isSubmitting = false;

    if (formRegra) {
        formRegra.addEventListener('submit', function(e) {
            e.preventDefault();
            if (isSubmitting) return;
            isSubmitting = true;

            const button = formRegra.querySelector('button[type="submit"]');
            button.disabled = true;
            button.textContent = 'Salvando...';
            successMessage.textContent = '';

            fetch(formRegra.action, { method: 'POST', body: new FormData(formRegra) })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    successMessage.textContent = data.message;
                    setTimeout(() => { successMessage.textContent = ''; }, 3000);
                } else {
                    alert('Erro: ' + data.message);
                }
            })
            .finally(() => {
                button.disabled = false;
                button.textContent = 'Salvar Regra';
                isSubmitting = false;
            });
        });
    }

    // --- LÓGICA ATUALIZADA PARA LIMPAR A URNA COM MODAL ---
    const modalLimpeza = document.getElementById('modal-confirmar-limpeza');
    const btnAbrirModalLimpeza = document.getElementById('btn-abrir-modal-limpeza');
    const btnCancelarLimpeza = document.getElementById('btn-cancelar-limpeza');
    const btnConfirmarLimpeza = document.getElementById('btn-confirmar-limpeza');

    if (btnAbrirModalLimpeza) {
        btnAbrirModalLimpeza.addEventListener('click', function() {
            modalLimpeza.classList.add('visible');
        });
    }

    const closeModal = () => modalLimpeza.classList.remove('visible');
    if (btnCancelarLimpeza) btnCancelarLimpeza.addEventListener('click', closeModal);
    if (modalLimpeza) modalLimpeza.addEventListener('click', e => { if (e.target === modalLimpeza) closeModal(); });

    if (btnConfirmarLimpeza) {
        btnConfirmarLimpeza.addEventListener('click', function() {
            btnConfirmarLimpeza.disabled = true;
            btnConfirmarLimpeza.textContent = 'Limpando...';

            fetch('php/limpar_sorteio.php', { method: 'POST' })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert(data.message);
                    window.location.reload(); // Recarrega a página para atualizar o contador
                } else {
                    alert('Erro: ' + data.message);
                    btnConfirmarLimpeza.disabled = false;
                    btnConfirmarLimpeza.textContent = 'Sim, Limpar Urna';
                }
            })
            .catch(error => {
                alert('Erro de conexão.');
                btnConfirmarLimpeza.disabled = false;
                btnConfirmarLimpeza.textContent = 'Sim, Limpar Urna';
            });
        });
    }
});
</script>

<?php include 'templates/footer.php'; ?>