<?php 
// /cadastro.php
session_start();
$cpf_cliente = $_SESSION['cpf_digitado'] ?? '';
include 'templates/header.php'; 
?>

<title>Faça seu Cadastro</title>

<div class="card-container">
    <h1>Faça seu Cadastro</h1>
    <p class="subtitle">Preencha seus dados para finalizar o cadastro e participar do sorteio.</p>
    
    <form id="cadastro-form" action="php/cadastrar_cliente.php" method="POST" style="width: 100%;">
        <input type="hidden" name="cpf" value="<?php echo htmlspecialchars($cpf_cliente); ?>">
        <div class="form-group">
            <label for="nome">Nome Completo</label>
            <input type="text" id="nome" name="nome" required>
        </div>
        <div class="form-group">
            <label for="whatsapp">WhatsApp</label>
            <input type="tel" id="whatsapp" name="whatsapp" placeholder="(XX) XXXXX-XXXX" required>
        </div>
        <div class="form-group">
            <label for="nascimento">Data de Nascimento</label>
            <input type="text" id="nascimento" name="nascimento" placeholder="DD/MM/AAAA" maxlength="10" required>
        </div>
        <p id="form-error-message" style="color: #D8000C; text-align: center; min-height: 20px;"></p>
        <button type="submit" class="btn btn-verde">Finalizar Cadastro</button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    const nascimentoInput = document.getElementById('nascimento');
    nascimentoInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length > 2) value = value.substring(0, 2) + '/' + value.substring(2);
        if (value.length > 5) value = value.substring(0, 5) + '/' + value.substring(5, 9);
        e.target.value = value;
    });

    const form = document.getElementById('cadastro-form');
    // --- INÍCIO DA CORREÇÃO ---
    let isSubmitting = false; // Cria uma "trava"

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (isSubmitting) return; // Se já estiver enviando, ignora o clique
        isSubmitting = true;      // Ativa a trava

        const button = form.querySelector('button[type="submit"]');
        const errorMessage = document.getElementById('form-error-message');
        
        button.disabled = true;
        button.textContent = 'Cadastrando...';
        errorMessage.textContent = '';

        const formData = new FormData(form);

        fetch(form.action, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                window.location.href = 'confirmacao_cliente.php';
            } else {
                errorMessage.textContent = data.message || 'Ocorreu um erro.';
                button.disabled = false;
                button.textContent = 'Finalizar Cadastro';
                isSubmitting = false; // Libera a trava em caso de erro
            }
        })
        .catch(error => {
            console.error('Erro na requisição:', error);
            errorMessage.textContent = 'Erro de conexão.';
            button.disabled = false;
            button.textContent = 'Finalizar Cadastro';
            isSubmitting = false; // Libera a trava em caso de erro
        });
    });
    // --- FIM DA CORREÇÃO ---
});
</script>

<?php include 'templates/footer.php'; ?>