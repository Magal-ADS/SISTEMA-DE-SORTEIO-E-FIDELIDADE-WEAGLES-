<?php
// /php/processa_compra_com_senha.php

session_start();
require_once "db_config.php";
header('Content-Type: application/json');

// --- 1. RECEBER E VALIDAR DADOS DE ENTRADA ---
$response = ['status' => 'error', 'message' => 'Ocorreu um erro desconhecido.'];

$cliente_id = $_POST['cliente_id'] ?? 0;
$vendedor_id = $_POST['vendedor_id'] ?? 0;
$valor_formatado = $_POST['valor'] ?? '0';
$senha_vendedor = $_POST['senha_vendedor'] ?? '';

if (empty($cliente_id) || empty($vendedor_id) || empty($valor_formatado) || empty($senha_vendedor)) {
    $response['message'] = 'Todos os campos, incluindo a senha, são obrigatórios.';
    echo json_encode($response);
    exit;
}

// --- 2. VERIFICAR A SENHA DO VENDEDOR ---
$sql_vendedor = "SELECT senha FROM usuarios WHERE id = ? AND CARGO = 2";
if ($stmt_vendedor = $link->prepare($sql_vendedor)) {
    $stmt_vendedor->bind_param("i", $vendedor_id);
    $stmt_vendedor->execute();
    $result_vendedor = $stmt_vendedor->get_result();

    if ($result_vendedor->num_rows === 1) {
        $vendedor_data = $result_vendedor->fetch_assoc();
        if (!password_verify($senha_vendedor, $vendedor_data['senha'])) {
            $response['message'] = 'Senha do vendedor incorreta. Tente novamente.';
            echo json_encode($response);
            exit;
        }
    } else {
        $response['message'] = 'Vendedor não encontrado ou inválido.';
        echo json_encode($response);
        exit;
    }
    $stmt_vendedor->close();
} else {
    $response['message'] = 'Erro ao verificar os dados do vendedor.';
    echo json_encode($response);
    exit;
}

// --- 3. PREPARAR DADOS E INICIAR TRANSAÇÃO ---
// Converte o valor "1.234,56" para o formato do banco "1234.56"
$valor_sem_ponto = str_replace('.', '', $valor_formatado);
$valor_para_banco = str_replace(',', '.', $valor_sem_ponto);

// O ID do admin/loja. No fluxo do cliente, assumimos 1 como padrão.
$usuario_id = 1; 

// Inicia a transação. Se algo der errado, tudo é desfeito.
$link->begin_transaction();

try {
    // --- 4. INSERIR NA TABELA `compras` ---
    $sql_compra = "INSERT INTO compras (cliente_id, valor, vendedor_id, usuario_id) VALUES (?, ?, ?, ?)";
    $stmt_compra = $link->prepare($sql_compra);
    $stmt_compra->bind_param("idii", $cliente_id, $valor_para_banco, $vendedor_id, $usuario_id);
    $stmt_compra->execute();
    
    // Pega o ID da compra que acabamos de inserir
    $compra_id = $link->insert_id;
    $stmt_compra->close();

    // --- 5. INSERIR NA TABELA `sorteio` ---
    $sql_sorteio = "INSERT INTO sorteio (cliente_id, compra_id, usuario_id) VALUES (?, ?, ?)";
    $stmt_sorteio = $link->prepare($sql_sorteio);
    $stmt_sorteio->bind_param("iii", $cliente_id, $compra_id, $usuario_id);
    
    // Insere o primeiro número da sorte (obrigatório)
    $stmt_sorteio->execute();

    // REGRA DE NEGÓCIO: Se a compra for >= R$ 50, insere o segundo número da sorte
    if ($valor_para_banco >= 50) {
        $stmt_sorteio->execute();
    }
    $stmt_sorteio->close();

    // --- 6. FINALIZAR TRANSAÇÃO ---
    // Se chegamos até aqui sem erros, confirma todas as operações no banco.
    $link->commit();

    $response = [
        'status' => 'success',
        'message' => 'Compra registrada e números da sorte gerados!',
        'redirect' => 'sucesso.php'
    ];

} catch (mysqli_sql_exception $exception) {
    // Se qualquer um dos passos acima falhar, desfaz tudo.
    $link->rollback();
    $response['message'] = 'Erro ao salvar os dados no banco. Nenhuma informação foi registrada.';
    // Para depuração, você pode querer logar o erro: error_log($exception->getMessage());
}

$link->close();
echo json_encode($response);

?>