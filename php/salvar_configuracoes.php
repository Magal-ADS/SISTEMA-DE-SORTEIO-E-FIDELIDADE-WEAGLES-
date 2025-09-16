<?php
// /php/salvar_configuracoes.php (Versão PostgreSQL)

session_start();
require_once "db_config.php";
header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Acesso não autorizado.'];

// Bloco de segurança (inalterado)
if (!isset($_SESSION['cargo']) || $_SESSION['cargo'] != 1) {
    echo json_encode($response);
    exit;
}

$novas_configuracoes = $_POST;

// 1. Inicia a transação com o comando BEGIN
pg_query($link, "BEGIN");

try {
    // 2. Prepara a query UMA VEZ, antes do loop
    $sql = "UPDATE configuracoes SET valor = $1 WHERE chave = $2";
    $stmt = pg_prepare($link, "salvar_config_query", $sql);

    if (!$stmt) {
        // Se a preparação falhar, não há como continuar
        throw new Exception('Falha ao preparar a consulta de atualização.');
    }

    // 3. Itera sobre cada configuração enviada
    foreach ($novas_configuracoes as $chave => $valor) {
        if (!empty($chave) && isset($valor)) {
            // Executa a query preparada para cada par chave/valor
            $result = pg_execute($link, "salvar_config_query", [$valor, $chave]);

            // 4. Se UMA execução falhar, lança um erro para acionar o CATCH
            if (!$result) {
                throw new Exception("Erro ao tentar salvar a configuração '{$chave}'.");
            }
        }
    }
    
    // 5. Se o loop terminar sem erros, commita a transação
    pg_query($link, "COMMIT");

    $response = ['status' => 'success', 'message' => 'Configurações salvas com sucesso!'];

} catch (Exception $exception) { // O catch agora pega uma Exception genérica
    // 6. Se qualquer erro ocorreu, desfaz TODAS as alterações
    pg_query($link, "ROLLBACK");
    $response['message'] = 'Erro ao salvar as configurações no banco de dados. Nenhuma alteração foi salva.';
}

// Fecha a conexão
pg_close($link);
echo json_encode($response);
?>