<?php
// /php/atualizar_cliente.php (VERSÃO FINAL COM AUTOCONFIRMAÇÃO FORÇADA)

session_start();
header('Content-Type: application/json');
require_once "db_config.php";

$response = ['status' => 'error', 'message' => 'Ocorreu um erro desconhecido.'];

// Validações iniciais (sessão, campos, senha)
if (!isset($_SESSION['cliente_id'])) { $response['message'] = 'Sessão do cliente não encontrada.'; echo json_encode($response); exit; }
$cliente_id = $_SESSION['cliente_id'];
$field_name = $_POST['field_name'] ?? '';
$new_value = trim($_POST['new_value'] ?? '');
$senha_geral_digitada = $_POST['senha_geral'] ?? '';
if (empty($field_name) || ($new_value === '' && $field_name !== 'data_nascimento') || empty($senha_geral_digitada)) { $response['message'] = 'Todos os campos são obrigatórios.'; echo json_encode($response); exit; }
$senha_correta_global = '1234';
if ($senha_geral_digitada !== $senha_correta_global) { $response['message'] = 'Senha de liberação incorreta.'; echo json_encode($response); exit; }
$allowed_fields = ['nome_completo', 'whatsapp', 'data_nascimento'];
if (!in_array($field_name, $allowed_fields)) { $response['message'] = 'Campo de edição inválido.'; echo json_encode($response); exit; }

// Tratamento e validação dos dados
$update_value_db = $new_value;
$display_value = $new_value;
if ($field_name === 'whatsapp') {
    $update_value_db = preg_replace('/[^0-9]/', '', $new_value);
    if (strlen($update_value_db) < 10 || strlen($update_value_db) > 11) { $response['message'] = 'Número de WhatsApp inválido.'; echo json_encode($response); exit; }
    $display_value = formatarWhatsApp($update_value_db);
} elseif ($field_name === 'data_nascimento') {
    if (empty($new_value)) { $update_value_db = null; $display_value = '--'; } else {
        $date_obj = DateTime::createFromFormat('d/m/Y', $new_value);
        if (!$date_obj || $date_obj->format('d/m/Y') !== $new_value) { $response['message'] = 'Formato de data inválido.'; echo json_encode($response); exit; }
        $update_value_db = $date_obj->format('Y-m-d');
        $display_value = $new_value;
    }
}


// =================== LÓGICA DE GRAVAÇÃO DIRETA ===================
try {
    $sql_update = "UPDATE clientes SET {$field_name} = $1 WHERE id = $2";
    $result_update = pg_query_params($link, $sql_update, [$update_value_db, $cliente_id]);

    if ($result_update) {
        $response = [
            'status' => 'success',
            'message' => 'Dado atualizado com sucesso!',
            'field_name' => $field_name,
            'new_value_formatted' => $display_value,
            'new_value_raw' => $new_value
        ];
    } else {
        // Se a query falhou, lança uma exceção
        throw new Exception(pg_last_error($link));
    }
} catch (Exception $e) {
    $error_message = $e->getMessage();
    if (strpos($error_message, '23505') !== false) {
        $response['message'] = 'Este valor (CPF ou WhatsApp) já está em uso por outro cliente.';
    } else {
        $response['message'] = 'Erro ao atualizar o dado no banco: ' . $error_message;
    }
}
// =================================================================

pg_close($link);
echo json_encode($response);


function formatarWhatsApp($whatsapp) {
    $whatsapp = preg_replace('/[^0-9]/', '', $whatsapp);
    if (strlen($whatsapp) == 11) {
        return '(' . substr($whatsapp, 0, 2) . ') ' . substr($whatsapp, 2, 5) . '-' . substr($whatsapp, 7, 4);
    } elseif (strlen($whatsapp) == 10) {
        return '(' . substr($whatsapp, 0, 2) . ') ' . substr($whatsapp, 2, 4) . '-' . substr($whatsapp, 6, 4);
    }
    return $whatsapp;
}
?>