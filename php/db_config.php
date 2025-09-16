<?php
// Mude para mostrar todos os erros (bom para depuração)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Pega a URL de conexão do Postgres da variável de ambiente do Heroku
$dbUrl = getenv('DATABASE_URL');

if ($dbUrl === false) {
    // --- AMBIENTE LOCAL (SEU COMPUTADOR COM XAMPP) ---
    // Se a DATABASE_URL não existe, usamos as configurações locais.
    $dbHost = 'localhost';
    $dbPort = '5432'; // Porta padrão do PostgreSQL
    $dbUser = 'postgres'; // Usuário padrão do PostgreSQL
    $dbPass = 'Cheldonegao2310'; // <-- MUITO IMPORTANTE: COLOQUE A SENHA QUE VOCÊ DEFINIU AO INSTALAR O POSTGRES
    $dbName = 'sorteio_magal_store'; // O nome do seu banco de dados local

} else {
    // --- AMBIENTE DE PRODUÇÃO (HEROKU) ---
    // Se a DATABASE_URL existe, usamos as configurações do Heroku.
    $dbInfo = parse_url($dbUrl);
    $dbHost = $dbInfo['host'];
    $dbPort = $dbInfo['port'];
    $dbUser = $dbInfo['user'];
    $dbPass = $dbInfo['pass'];
    $dbName = ltrim($dbInfo['path'], '/');
}

// Monta a string de conexão para o PostgreSQL (funciona para ambos os ambientes)
$connection_string = "host={$dbHost} port={$dbPort} dbname={$dbName} user={$dbUser} password={$dbPass}";

// Tenta criar a conexão
$link = pg_connect($connection_string);

// Checa se a conexão foi bem-sucedida
if (!$link) {
    // Adiciona a variável de erro do postgres para mais detalhes
    die("Falha na conexão com o banco de dados: " . pg_last_error($link));
}

// Garante que a comunicação use o formato UTF-8
pg_set_client_encoding($link, "UTF8");

?>