<?php
// /php/salvar_configuracoes.php (Corrigido)

session_start();
require_once "db_config.php";
header('Content-Type: application/json');

// Linha corrigida: 'status' sem o 's' extra
$response = ['status' => 'error', 'message' => 'Ocorreu um erro.'];

if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['usuario_cargo']) || $_SESSION['usuario_cargo'] != 1) {
    $response['message'] = 'Acesso não autorizado.';
    echo json_encode($response);
    exit;
}

$novas_configuracoes = $_POST;
$link->begin_transaction();

try {
    $sql = "UPDATE configuracoes SET valor = ? WHERE chave = ?";
    $stmt = $link->prepare($sql);

    foreach ($novas_configuracoes as $chave => $valor) {
        if (!empty($chave) && isset($valor)) { // Permite valor 0
            $stmt->bind_param("ss", $valor, $chave);
            $stmt->execute();
        }
    }
    
    $stmt->close();
    $link->commit();

    $response = ['status' => 'success', 'message' => 'Configurações salvas com sucesso!'];

} catch (mysqli_sql_exception $exception) {
    $link->rollback();
    $response['message'] = 'Erro ao salvar as configurações no banco de dados.';
}

$link->close();
echo json_encode($response);
?>