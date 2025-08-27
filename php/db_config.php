<?php
// Mude para mostrar todos os erros (bom para depuração)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Configurações do banco de dados
$db_server = 'localhost';
$db_username = 'root';
$db_password = ''; // Senha padrão do XAMPP é vazia
$db_name = 'sorteio_magal_store'; // Verifique se não há espaços aqui

// Criar a conexão usando mysqli
$link = new mysqli($db_server, $db_username, $db_password, $db_name);

// Checar a conexão
if ($link->connect_error) {
    // Se houver um erro de conexão, o script para aqui e envia uma resposta JSON
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Falha na conexão com o banco de dados: ' . $link->connect_error]);
    exit(); // Para a execução
}

// Define o charset para utf8 para evitar problemas com acentos
$link->set_charset("utf8");

?>