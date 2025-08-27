<?php
// Mude para mostrar todos os erros (bom para depuração)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Pega a URL de conexão do banco de dados da variável de ambiente do Heroku
$dbUrl = getenv('JAWSDB_URL');

if ($dbUrl === false) {
    // --- AMBIENTE LOCAL (SEU COMPUTADOR COM XAMPP) ---
    // O código usará essas credenciais se não encontrar a variável do Heroku.
    $db_server = 'localhost';
    $db_username = 'root';
    $db_password = '';
    $db_name = 'sorteio_magal_store';

} else {
    // --- AMBIENTE DE PRODUÇÃO (HEROKU) ---
    // O código usará essas credenciais quando estiver rodando no Heroku.
    $dbInfo = parse_url($dbUrl);
    $db_server = $dbInfo['host'];
    $db_username = $dbInfo['user'];
    $db_password = $dbInfo['pass'];
    $db_name = ltrim($dbInfo['path'], '/');
}

// Criar a conexão usando as variáveis definidas acima
$link = new mysqli($db_server, $db_username, $db_password, $db_name);

// Checar a conexão
if ($link->connect_error) {
    // Se houver um erro de conexão, o script para aqui.
    // Em vez de JSON, vamos mostrar uma mensagem de erro mais clara para o navegador.
    die('Falha na conexão com o banco de dados: ' . $link->connect_error);
}

// Define o charset para utf8 para evitar problemas com acentos
$link->set_charset("utf8");

?>