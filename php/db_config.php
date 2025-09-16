<?php
// Mude para mostrar todos os erros (bom para depuração)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Pega a URL de conexão do Postgres da variável de ambiente do Heroku
$dbUrl = getenv('DATABASE_URL');

// Se a variável não existir, o app para com um erro claro.
if ($dbUrl === false) {
    die("Erro crítico: A variável de ambiente DATABASE_URL não foi encontrada. Verifique as configurações do Heroku.");
}
    
// "Traduz" a URL para as partes que a conexão precisa
$dbInfo = parse_url($dbUrl);
$dbHost = $dbInfo['host'];
$dbPort = $dbInfo['port'];
$dbUser = $dbInfo['user'];
$dbPass = $dbInfo['pass'];
$dbName = ltrim($dbInfo['path'], '/');

// Monta a string de conexão para o PostgreSQL
$connection_string = "host={$dbHost} port={$dbPort} dbname={$dbName} user={$dbUser} password={$dbPass}";

// Tenta criar a conexão usando a função específica do Postgres: pg_connect
$link = pg_connect($connection_string);

// Checa se a conexão foi bem-sucedida
if (!$link) {
    die("Falha na conexão com o banco de dados PostgreSQL.");
}

// Garante que a comunicação use o formato UTF-8 para evitar problemas com acentos
pg_set_client_encoding($link, "UTF8");

?>