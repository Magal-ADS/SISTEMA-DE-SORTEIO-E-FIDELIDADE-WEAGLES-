<?php 
session_start();
unset($_SESSION['cliente_id']);
unset($_SESSION['cliente_nome']);
unset($_SESSION['cpf_cliente']);
unset($_SESSION['cpf_digitado']);

include 'templates/header.php'; 
?>

<title>Verificar CPF</title>

<div class="card-container">
    <h1>Verificação de CPF</h1>
    <p class="subtitle">Digite seu CPF para continuar. Se você já for cliente, seus dados serão preenchidos.</p>
    
    <form id="cpf-form" style="width: 100%;">
        <div class="form-group">
            <label for="cpf">CPF</label>
            <input type="text" id="cpf" name="cpf" placeholder="000.000.000-00" required 
                   maxlength="14" inputmode="numeric">
        </div>
        
        <p id="error-message" style="color: #D8000C; text-align: center; min-height: 20px;"></p>

        <button type="submit" class="btn btn-laranja" id="btn-prosseguir">Prosseguir</button>
    </form>
</div>

<script>
// ==================== MÁSCARA DE CPF (VERSÃO CORRIGIDA) ====================
document.getElementById('cpf').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '').substring(0, 11);
    const chars = value.split('');
    let formattedValue = '';

    chars.forEach((char, index) => {
        if (index === 3 || index === 6) {
            formattedValue += '.';
        }
        if (index === 9) {
            formattedValue += '-';
        }
        formattedValue += char;
    });
    
    e.target.value = formattedValue;
});


// ==================== LÓGICA AJAX (sem alterações) ====================
document.getElementById('cpf-form').addEventListener('submit', function(event) {
    event.preventDefault();
    const cpfInput = document.getElementById('cpf');
    const submitButton = document.getElementById('btn-prosseguir');
    const errorMessage = document.getElementById('error-message');

    errorMessage.textContent = '';
    submitButton.disabled = true;
    submitButton.textContent = 'Verificando...';

    const formData = new FormData();
    // Importante: Mesmo com a máscara, o PHP receberá o valor com pontos e traços.
    // O seu script verificar_cpf.php deve estar preparado para limpar a string.
    formData.append('cpf', cpfInput.value);

    fetch('php/verificar_cpf.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) throw new Error('Erro de rede ou servidor.');
        return response.json();
    })
    .then(data => {
        if (data.status === 'exists' || data.status === 'not_exists') {
            window.location.href = data.redirect;
        } else {
            errorMessage.textContent = data.message || 'Ocorreu um erro inesperado.';
            submitButton.disabled = false;
            submitButton.textContent = 'Prosseguir';
        }
    })
    .catch(error => {
        console.error('Erro na requisição fetch:', error);
        errorMessage.textContent = 'Não foi possível se comunicar com o servidor. Tente novamente.';
        submitButton.disabled = false;
        submitButton.textContent = 'Prosseguir';
    });
});
</script>

<?php include 'templates/footer.php'; ?>