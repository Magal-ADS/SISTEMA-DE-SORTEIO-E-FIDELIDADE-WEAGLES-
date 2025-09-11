<?php
// /php/salvar_configuracoes.php

session_start();
require_once "db_config.php";
header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Acesso não autorizado.'];

// BLOCO DE SEGURANÇA ATUALIZADO
// Segurança: Apenas um admin logado (CARGO = 1) pode salvar configurações.
if (!isset($_SESSION['cargo']) || $_SESSION['cargo'] != 1) {
    echo json_encode($response);
    exit;
}

// O resto do seu código, 100% intacto
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