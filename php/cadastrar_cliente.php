<?php
// /php/cadastrar_cliente.php (VERSÃO COM TRATAMENTO DE ERRO CORRIGIDO)

session_start();
require_once "db_config.php";
header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Ocorreu um erro desconhecido.'];

$usuario_id = 1; 

$cpf_da_sessao = $_SESSION['cpf_digitado'] ?? '';
$nome = trim($_POST['nome'] ?? '');
$whatsapp_sujo = trim($_POST['whatsapp'] ?? '');
$nascimento_br = trim($_POST['nascimento'] ?? ''); 

$cpf_limpo = preg_replace('/[^0-9]/', '', $cpf_da_sessao);
$whatsapp_limpo = preg_replace('/[^0-9]/', '', $whatsapp_sujo);

if (empty($cpf_limpo) || empty($nome) || empty($whatsapp_limpo) || empty($nascimento_br)) {
    $response['message'] = 'Todos os campos são obrigatórios.';
    echo json_encode($response);
    exit;
}

$date_obj = DateTime::createFromFormat('d/m/Y', $nascimento_br);
if (!$date_obj || $date_obj->format('d/m/Y') !== $nascimento_br) {
    $response['message'] = 'Data de nascimento inválida. Use o formato DD/MM/AAAA.';
    echo json_encode($response);
    exit;
}
$nascimento_para_banco = $date_obj->format('Y-m-d');

$sql = "INSERT INTO clientes (cpf, nome_completo, whatsapp, data_nascimento, usuario_id) VALUES ($1, $2, $3, $4, $5) RETURNING id";

$stmt = pg_prepare($link, "cadastrar_cliente_query", $sql);
if ($stmt) {
    // A @ suprime o Warning do PHP, pois vamos tratar o erro manualmente
    $result = @pg_execute($link, "cadastrar_cliente_query", array($cpf_limpo, $nome, $whatsapp_limpo, $nascimento_para_banco, $usuario_id));
    
    if ($result && pg_num_rows($result) > 0) {
        // Bloco de sucesso (inalterado)
        $row = pg_fetch_assoc($result);
        $_SESSION['cliente_id'] = $row['id'];
        $_SESSION['cliente_nome'] = $nome;
        unset($_SESSION['cpf_digitado']);
        $response = ['status' => 'success', 'message' => 'Cliente cadastrado com sucesso!', 'redirect' => 'dados_compra.php'];
    
    } else {
        // =================== BLOCO DE ERRO CORRIGIDO ===================
        // Se a execução falhou ($result é false), pegamos o erro da conexão.
        $error_message = pg_last_error($link);
        
        // Verificamos se a mensagem de erro contém o código de violação de unicidade (23505)
        if (strpos($error_message, '23505') !== false) {
             $response['message'] = 'Este CPF já está cadastrado.';
        } else {
             $response['message'] = 'Erro ao cadastrar o cliente no banco de dados.';
        }
        // ===============================================================
    }
} else {
    $response['message'] = 'Erro na preparação da consulta.';
}

pg_close($link);
echo json_encode($response);
?>