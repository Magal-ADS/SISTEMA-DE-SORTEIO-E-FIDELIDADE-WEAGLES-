<?php
// /base_clientes.php (VERSÃO CORRIGIDA PARA POSTGRESQL)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['cargo']) || $_SESSION['cargo'] != 1) {
    header("Location: login.php");
    exit();
}

include 'templates/header.php';
require_once 'php/db_config.php';

// --- BUSCA AS CONFIGURAÇÕES (Convertido para PG) ---
$configs = [];
$result_configs = pg_query($link, "SELECT chave, valor FROM configuracoes WHERE chave LIKE 'filtro_%'");
if ($result_configs) {
    while($row = pg_fetch_assoc($result_configs)){
        $configs[$row['chave']] = $row['valor'];
    }
}
$inativos_meses = $configs['filtro_inativos_meses'] ?? 6;
$gastos_altos_valor = $configs['filtro_gastos_altos_valor'] ?? 1000;
$gastos_altos_dias = $configs['filtro_gastos_altos_dias'] ?? 90;

// --- LÓGICA DOS FILTROS (Convertido para PG) ---
$filtro_ativo = $_GET['filtro'] ?? 'todos';
$clientes = [];
$admin_id = $_SESSION['usuario_id'];
$sql = '';
$params = [];
$query_name = '';

switch ($filtro_ativo) {
    case 'aniversariantes_dia':
        // MUDANÇA: Funções de data do MySQL trocadas por EXTRACT do PostgreSQL
        $sql = "SELECT nome_completo, whatsapp, data_nascimento, data_cadastro 
                FROM clientes 
                WHERE usuario_id = $1 
                AND EXTRACT(DAY FROM data_nascimento) = EXTRACT(DAY FROM CURRENT_DATE) 
                AND EXTRACT(MONTH FROM data_nascimento) = EXTRACT(MONTH FROM CURRENT_DATE)";
        $query_name = 'admin_filtro_aniversariantes';
        $params = [$admin_id];
        break;

    case 'inativos':
        // MUDANÇA: DATE_SUB trocado pelo operador de intervalo do PostgreSQL
        $sql = "SELECT c.nome_completo, c.whatsapp, c.data_nascimento, c.data_cadastro 
                FROM clientes c 
                LEFT JOIN ( 
                    SELECT cliente_id, MAX(data_compra) as ultima_compra 
                    FROM compras 
                    GROUP BY cliente_id 
                ) AS ultimas_compras ON c.id = ultimas_compras.cliente_id 
                WHERE c.usuario_id = $1 
                AND (ultimas_compras.ultima_compra IS NULL OR ultimas_compras.ultima_compra < (CURRENT_DATE - ($2 || ' months')::interval)) 
                ORDER BY c.nome_completo ASC";
        $query_name = 'admin_filtro_inativos';
        $params = [$admin_id, $inativos_meses];
        break;

    case 'gastos_altos':
        // MUDANÇA: DATE_SUB trocado pelo operador de intervalo do PostgreSQL
        $sql = "SELECT c.nome_completo, c.whatsapp, c.data_nascimento, c.data_cadastro, SUM(co.valor) AS total_gasto 
                FROM clientes c 
                JOIN compras co ON c.id = co.cliente_id 
                WHERE c.usuario_id = $1 
                AND co.data_compra >= (CURRENT_DATE - ($2 || ' days')::interval) 
                GROUP BY c.id 
                HAVING SUM(co.valor) >= $3 
                ORDER BY total_gasto DESC";
        $query_name = 'admin_filtro_gastos_altos';
        $params = [$admin_id, $gastos_altos_dias, $gastos_altos_valor];
        break;

    case 'todos':
    default:
        $sql = "SELECT nome_completo, whatsapp, data_nascimento, data_cadastro 
                FROM clientes 
                WHERE usuario_id = $1 
                ORDER BY data_cadastro DESC";
        $query_name = 'admin_filtro_todos';
        $params = [$admin_id];
        break;
}

// Execução unificada da consulta
$stmt = pg_prepare($link, $query_name, $sql);
if ($stmt) {
    $result = pg_execute($link, $query_name, $params);
    if ($result) {
        // MUDANÇA: fetch_all trocado por um loop com pg_fetch_assoc
        while ($row = pg_fetch_assoc($result)) {
            $clientes[] = $row;
        }
    }
}
pg_close($link);
?>

<title>Base de Clientes</title>

<style>
    .page-header h1 { color: var(--cor-dourado) !important; }
    .page-header p { color: var(--cor-branco) !important; opacity: 0.8; }
    .filter-nav a { background-color: rgba(255, 255, 255, 0.1); color: var(--cor-branco); border-color: rgba(255, 255, 255, 0.2); }
    .filter-nav a:hover { background-color: rgba(255, 255, 255, 0.2); border-color: var(--cor-dourado); }
    .filter-nav a.active { background: var(--cor-dourado) !important; color: var(--cor-texto-principal) !important; }
    .table-wrapper { background-color: rgba(44, 44, 44, 0.5); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.1); }
    .data-table th, .data-table td { color: var(--cor-branco) !important; border-bottom: 1px solid rgba(255, 255, 255, 0.1); }
    .data-table th { opacity: 0.9; }
    .data-table td { opacity: 0.7; }
</style>
<div class="page-container">
    <header class="page-header">
        <h1>Base de Clientes</h1>
        <p>Visualize e filtre todos os clientes cadastrados em sua loja.</p>
    </header>

    <nav class="filter-nav">
        <a href="base_clientes.php?filtro=todos" class="<?php echo ($filtro_ativo == 'todos') ? 'active' : ''; ?>">Todos</a>
        <a href="base_clientes.php?filtro=aniversariantes_dia" class="<?php echo ($filtro_ativo == 'aniversariantes_dia') ? 'active' : ''; ?>">Aniversariantes do Dia</a>
        <a href="base_clientes.php?filtro=gastos_altos" class="<?php echo ($filtro_ativo == 'gastos_altos') ? 'active' : ''; ?>">
            Gastaram +R$<?php echo $gastos_altos_valor; ?> (<?php echo $gastos_altos_dias; ?>d)
        </a>
        <a href="base_clientes.php?filtro=inativos" class="<?php echo ($filtro_ativo == 'inativos') ? 'active' : ''; ?>">
            Inativos (+<?php echo $inativos_meses; ?> meses)
        </a>
    </nav>

    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Nome Completo</th>
                    <th>WhatsApp</th>
                    <th>Data de Nascimento</th>
                    <th>Cliente Desde</th>
                    <?php if ($filtro_ativo == 'gastos_altos'): ?>
                        <th>Total Gasto (<?php echo $gastos_altos_dias; ?>d)</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($clientes)): ?>
                    <tr><td colspan="5" style="text-align:center;">Nenhum cliente encontrado para este filtro.</td></tr>
                <?php else: ?>
                    <?php foreach ($clientes as $cliente): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($cliente['nome_completo']); ?></td>
                            <td><?php echo htmlspecialchars($cliente['whatsapp']); ?></td>
                            <td><?php echo date('d/m', strtotime($cliente['data_nascimento'])); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($cliente['data_cadastro'])); ?></td>
                            <?php if ($filtro_ativo == 'gastos_altos'): ?>
                                <td><?php echo 'R$ ' . number_format($cliente['total_gasto'], 2, ',', '.'); ?></td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
include 'templates/footer.php';
?>