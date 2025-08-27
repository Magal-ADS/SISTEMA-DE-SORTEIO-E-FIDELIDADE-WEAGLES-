<?php
// /gerenciar_filtros.php

session_start();
// Segurança: Apenas o Admin (CARGO = 1) pode acessar.
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['usuario_cargo']) || $_SESSION['usuario_cargo'] != 1) {
    header("Location: login.php");
    exit();
}

require_once 'php/db_config.php';

// Busca todas as configurações de filtros salvas no banco
$configuracoes = [];
$result = $link->query("SELECT chave, valor, descricao, tipo_input FROM configuracoes WHERE chave LIKE 'filtro_%'");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $configuracoes[$row['chave']] = $row;
    }
}
$link->close();

include 'templates/header.php';
?>

<title>Gerenciar Filtros</title>

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
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('form-filtros');
    const successMessage = document.getElementById('form-success-message');
    let isSubmitting = false; // Trava de segurança para evitar envios duplos

    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (isSubmitting) {
                return; // Se já estiver enviando, ignora o clique
            }
            isSubmitting = true; // Ativa a trava

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
                // Sempre executa, dando certo ou errado
                button.disabled = false;
                button.textContent = 'Salvar Alterações';
                isSubmitting = false; // Libera a trava para um novo envio
            });
        });
    }
});
</script>


<?php
// Inclui o rodapé
include 'templates/footer.php';
?>