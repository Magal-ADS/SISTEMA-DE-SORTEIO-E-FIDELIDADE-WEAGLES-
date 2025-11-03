<?php
// /dados_compra.php (VERSÃO FINAL COM FILTRO DE VENDEDORES ATIVOS)

session_start();

if (!isset($_SESSION['vendedor_autenticado']) || $_SESSION['vendedor_autenticado'] !== true) {
    header('Location: cpf.php');
    exit();
}

require_once 'php/db_config.php';

$nome_cliente = htmlspecialchars($_SESSION['cliente_nome']);

$vendedores = [];

// =================== ALTERAÇÃO IMPORTANTE AQUI ===================
// Adicionamos "AND ativo = TRUE" para buscar apenas os vendedores ativos.
$sql = "SELECT id, nome FROM usuarios WHERE cargo = 2 AND ativo = TRUE ORDER BY nome ASC";

$result = pg_query($link, $sql);
if ($result) {
    while ($row = pg_fetch_assoc($result)) {
        $vendedores[] = $row;
    }
}
pg_close($link);

include 'templates/header.php';
?>

<title>Registrar Compra</title>

<div class="card-container">
    <h1>Olá, <?php echo $nome_cliente; ?>!</h1>
    <p class="subtitle">Informe o valor e quem realizou a venda para finalizar.</p>
    
    <form id="form-finalizar-compra" action="php/finalizar_compra.php" method="POST" style="width: 100%;">
        <input type="hidden" name="cliente_id" value="<?php echo $_SESSION['cliente_id']; ?>">

        <div class="form-group">
            <label for="valor">Valor da compra (R$)</label>
            
            <input type="text" id="valor" name="valor" placeholder="0,00" required inputmode="numeric" autocomplete="off">
        
        </div>

        <div class="form-group">
            <label for="vendedor">Vendedor(a) que realizou a venda</label>
            <select id="vendedor" name="vendedor_id" required>
                <option value="" disabled selected>Selecione uma opção</option>
                <?php foreach ($vendedores as $vendedor): ?>
                    <option value="<?php echo $vendedor['id']; ?>"><?php echo htmlspecialchars($vendedor['nome']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <p id="form-error-message" class="modal-error"></p>
        <button type="submit" class="btn btn-verde">Registrar e Participar</button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    const valorInput = document.getElementById('valor');
    valorInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (!value) return;
        const numericValue = parseInt(value, 10) / 100;
        e.target.value = numericValue.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    });

    const form = document.getElementById('form-finalizar-compra');
    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const button = form.querySelector('button[type="submit"]');
        const errorMessage = document.getElementById('form-error-message');
        
        button.disabled = true;
        button.textContent = 'Registrando...';
        errorMessage.textContent = '';

        const formData = new FormData(form);

        fetch(form.action, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                window.location.href = data.redirect;
            } else {
                errorMessage.textContent = data.message || 'Ocorreu um erro.';
                button.disabled = false;
                button.textContent = 'Registrar e Participar';
            }
        })
        .catch(error => {
            console.error('Erro na requisição:', error);
            errorMessage.textContent = 'Erro de conexão.';
            button.disabled = false;
            button.textContent = 'Registrar e Participar';
        });
    });
});
</script>

<?php include 'templates/footer.php'; ?>