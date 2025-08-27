<?php 
// É uma boa prática iniciar a sessão e limpar dados antigos
// para garantir que cada fluxo de cliente comece do zero.
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
            <input type="text" id="cpf" name="cpf" placeholder="000.000.000-00" required>
        </div>
        
        <p id="error-message" style="color: #D8000C; text-align: center; min-height: 20px;"></p>

        <button type="submit" class="btn btn-laranja" id="btn-prosseguir">Prosseguir</button>
    </form>
</div>

<script>
// Adiciona um "ouvinte" ao evento de submissão do formulário
document.getElementById('cpf-form').addEventListener('submit', function(event) {
    // 1. Previne o comportamento padrão do formulário (que seria recarregar a página)
    event.preventDefault();

    // Seleciona os elementos do formulário que vamos usar
    const cpfInput = document.getElementById('cpf');
    const submitButton = document.getElementById('btn-prosseguir');
    const errorMessage = document.getElementById('error-message');

    // Limpa mensagens de erro anteriores e desabilita o botão
    errorMessage.textContent = '';
    submitButton.disabled = true;
    submitButton.textContent = 'Verificando...';

    // 2. Cria os dados do formulário para enviar na requisição
    const formData = new FormData();
    formData.append('cpf', cpfInput.value);

    // 3. Usa a API fetch para fazer a chamada assíncrona (AJAX) para o script PHP
    fetch('php/verificar_cpf.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        // Checa se a resposta da rede foi bem sucedida
        if (!response.ok) {
            throw new Error('Erro de rede ou servidor.');
        }
        return response.json(); // Converte a resposta em um objeto JSON
    })
    .then(data => {
        // 4. Processa os dados recebidos do PHP
        if (data.status === 'exists' || data.status === 'not_exists') {
            // Se o status for de sucesso, redireciona para a página indicada pelo PHP
            window.location.href = data.redirect;
        } else {
            // Se o PHP retornar um erro, mostra a mensagem e reativa o botão
            errorMessage.textContent = data.message || 'Ocorreu um erro inesperado.';
            submitButton.disabled = false;
            submitButton.textContent = 'Prosseguir';
        }
    })
    .catch(error => {
        // Captura erros de conexão ou falhas na requisição
        console.error('Erro na requisição fetch:', error);
        errorMessage.textContent = 'Não foi possível se comunicar com o servidor. Tente novamente.';
        submitButton.disabled = false;
        submitButton.textContent = 'Prosseguir';
    });
});
</script>

<?php include 'templates/footer.php'; ?>