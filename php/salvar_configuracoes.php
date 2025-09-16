<?php
// /php/salvar_configuracoes.php (VERSÃO FINAL E CORRIGIDA)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "db_config.php";
header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Acesso não autorizado.'];

// CORREÇÃO: Bloco de segurança padronizado
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['cargo']) || $_SESSION['cargo'] != 1) {
    echo json_encode($response);
    exit;
}

$novas_configuracoes = $_POST;

pg_query($link, "BEGIN");

try {
    $sql = "UPDATE configuracoes SET valor = $1 WHERE chave = $2";
    $stmt = pg_prepare($link, "save_config_query", $sql);

    if (!$stmt) {
        throw new Exception('Falha ao preparar a consulta de atualização.');
    }

    foreach ($novas_configuracoes as $chave => $valor) {
        if (!empty($chave) && isset($valor)) {
            $result = pg_execute($link, "save_config_query", [$valor, $chave]);

            if (!$result) {
                throw new Exception("Erro ao tentar salvar a configuração '{$chave}'.");
            }
        }
    }
    
    pg_query($link, "COMMIT");

    $response = ['status' => 'success', 'message' => 'Configurações salvas com sucesso!'];

} catch (Exception $exception) {
    pg_query($link, "ROLLBACK");
    $response['message'] = 'Erro ao salvar as configurações no banco de dados. Nenhuma alteração foi salva.';
}

pg_close($link);
echo json_encode($response);
?>