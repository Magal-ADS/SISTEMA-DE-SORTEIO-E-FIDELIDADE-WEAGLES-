<?php
// /gerenciar_filtros.php (VERSÃO CORRIGIDA PARA POSTGRESQL)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['cargo']) || $_SESSION['cargo'] != 1) {
    header("Location: login.php");
    exit();
}

require_once 'php/db_config.php';

// =================== INÍCIO DO BLOCO CORRIGIDO ===================
$configuracoes = [];
// A consulta SQL com LIKE é compatível com PostgreSQL
$sql = "SELECT chave, valor, descricao, tipo_input FROM configuracoes WHERE chave LIKE 'filtro_%'";

// A execução da consulta foi trocada para pg_query e pg_fetch_assoc
$result = pg_query($link, $sql);
if ($result) {
    while ($row = pg_fetch_assoc($result)) {
        $configuracoes[$row['chave']] = $row;
    }
}

pg_close($link);
// ==================== FIM DO BLOCO CORRIGIDO =====================

include 'templates/header.php';
?>

<title>Gerenciar Filtros</title>

<style>
    /* Estilos para o tema escuro */
    .page-header h1 { color: var(--cor-dourado) !important; }
    .page-header p { color: var(--cor-branco) !important; opacity: 0.8; }

    /* Adapta o formulário */
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
        <h1>Gerenciar Filtros</h1>
        <p>Ajuste os parâmetros usados para segmentar sua base de clientes.</p>
    </header>

    <form id="form-filtros" action="php/salvar_configuracoes.php" method="POST" class="settings-form">
        <div class="form-group">
            <label for="filtro_inativos_meses"><?php echo htmlspecialchars($configuracoes['filtro_inativos_meses']['descricao']); ?></label>
            <input type="number" name="filtro_inativos_meses" id="filtro_inativos_meses" value="<?php echo htmlspecialchars($configuracoes['filtro_inativos_meses']['valor']); ?>" required>
        </div>
        <div class="form-group">
            <label for="filtro_gastos_altos_valor"><?php echo htmlspecialchars($configuracoes['filtro_gastos_altos_valor']['descricao']); ?></label>
            <input type="number" step="0.01" name="filtro_gastos_altos_valor" id="filtro_gastos_altos_valor" value="<?php echo htmlspecialchars($configuracoes['filtro_gastos_altos_valor']['valor']); ?>" required>
        </div>
        <div class="form-group">
            <label for="filtro_gastos_altos_dias"><?php echo htmlspecialchars($configuracoes['filtro_gastos_altos_dias']['descricao']); ?></label>
            <input type="number" name="filtro_gastos_altos_dias" id="filtro_gastos_altos_dias" value="<?php echo htmlspecialchars($configuracoes['filtro_gastos_altos_dias']['valor']); ?>" required>
        </div>
        <button type="submit" class="btn btn-verde" id="btn-salvar-filtros">Salvar Alterações</button>
        <p id="form-success-message" class="success-message"></p>
    </form>
</div>

<script>
// O JavaScript não precisa de alteração
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('form-filtros');
    const successMessage = document.getElementById('form-success-message');
    let isSubmitting = false; 

    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (isSubmitting) {
                return; 
            }
            isSubmitting = true; 

            const button = document.getElementById('btn-salvar-filtros');
            button.disabled = true;
            button.textContent = 'Salvando...';
            successMessage.textContent = '';

            const formData = new FormData(form);

            fetch(form.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    successMessage.textContent = data.message;
                    setTimeout(() => { successMessage.textContent = ''; }, 3000);
                } else {
                    alert('Erro: ' + (data.message || 'Ocorreu um erro desconhecido.'));
                }
            })
            .catch(error => {
                console.error('Erro de conexão:', error);
                alert('Não foi possível se conectar ao servidor.');
            })
            .finally(() => {
                button.disabled = false;
                button.textContent = 'Salvar Alterações';
                isSubmitting = false;
            });
        });
    }
});
</script>

<?php
include 'templates/footer.php';
?>