<?php
// /base_clientes_vendedor.php

// 1. BLOCO DE SEGURANÇA (AJUSTADO PARA VENDEDORA)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Verifica se o usuário está logado E se o cargo é de Vendedora (2)
if (!isset($_SESSION['cargo']) || $_SESSION['cargo'] != 2) {
    header("Location: login_vendedora.php");
    exit();
}

include 'templates/header.php';
require_once 'php/db_config.php';

// --- BUSCA AS CONFIGURAÇÕES DOS FILTROS NO BANCO ---
$configs = [];
$result_configs = $link->query("SELECT chave, valor FROM configuracoes WHERE chave LIKE 'filtro_%'");
if ($result_configs) {
    while($row = $result_configs->fetch_assoc()){
        $configs[$row['chave']] = $row['valor'];
    }
}
$inativos_meses = $configs['filtro_inativos_meses'] ?? 6;
$gastos_altos_valor = $configs['filtro_gastos_altos_valor'] ?? 1000;
$gastos_altos_dias = $configs['filtro_gastos_altos_dias'] ?? 90;

// LÓGICA DOS FILTROS (REESCRITA PARA SER MAIS PRECISA)
$filtro_ativo = $_GET['filtro'] ?? 'todos';
$clientes = [];
$vendedora_id = $_SESSION['usuario_id'];

switch ($filtro_ativo) {
    case 'aniversariantes_dia':
        $sql = "SELECT DISTINCT c.nome_completo, c.whatsapp, c.data_nascimento, c.data_cadastro 
                FROM clientes c JOIN compras co ON c.id = co.cliente_id
                WHERE co.vendedor_id = ? AND DAY(c.data_nascimento) = DAY(CURDATE()) AND MONTH(c.data_nascimento) = MONTH(CURDATE())";
        $stmt = $link->prepare($sql);
        $stmt->bind_param("i", $vendedora_id);
        break;

    case 'inativos':
        $sql = "SELECT c.nome_completo, c.whatsapp, c.data_nascimento, c.data_cadastro
                FROM clientes c
                JOIN (
                    SELECT cliente_id, MAX(data_compra) as ultima_compra_com_vendedor
                    FROM compras
                    WHERE vendedor_id = ?
                    GROUP BY cliente_id
                ) AS ultimas_compras ON c.id = ultimas_compras.cliente_id
                WHERE ultimas_compras.ultima_compra_com_vendedor < DATE_SUB(CURDATE(), INTERVAL ? MONTH)
                ORDER BY c.nome_completo ASC";
        $stmt = $link->prepare($sql);
        $stmt->bind_param("ii", $vendedora_id, $inativos_meses);
        break;

    case 'gastos_altos':
        $sql = "SELECT c.nome_completo, c.whatsapp, c.data_nascimento, c.data_cadastro, SUM(co.valor) AS total_gasto
                FROM clientes c JOIN compras co ON c.id = co.cliente_id
                WHERE co.vendedor_id = ? AND co.data_compra >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                GROUP BY c.id
                HAVING SUM(co.valor) >= ?
                ORDER BY total_gasto DESC";
        $stmt = $link->prepare($sql);
        $stmt->bind_param("iid", $vendedora_id, $gastos_altos_dias, $gastos_altos_valor);
        break;

    case 'todos':
    default:
        $sql = "SELECT DISTINCT c.nome_completo, c.whatsapp, c.data_nascimento, c.data_cadastro 
                FROM clientes c JOIN compras co ON c.id = co.cliente_id
                WHERE co.vendedor_id = ? 
                ORDER BY c.data_cadastro DESC";
        $stmt = $link->prepare($sql);
        $stmt->bind_param("i", $vendedora_id);
        break;
}

$stmt->execute();
$result = $stmt->get_result();
if ($result) { $clientes = $result->fetch_all(MYSQLI_ASSOC); }
$stmt->close(); $link->close();
?>

<title>Minha Base de Clientes</title>

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
        <h1>Minha Base de Clientes</h1>
        <p>Visualize todos os clientes que compraram com você.</p>
    </header>

    <nav class="filter-nav">
        <a href="base_clientes_vendedor.php?filtro=todos" class="<?php echo ($filtro_ativo == 'todos') ? 'active' : ''; ?>">Todos</a>
        <a href="base_clientes_vendedor.php?filtro=aniversariantes_dia" class="<?php echo ($filtro_ativo == 'aniversariantes_dia') ? 'active' : ''; ?>">Aniversariantes do Dia</a>
        <a href="base_clientes_vendedor.php?filtro=gastos_altos" class="<?php echo ($filtro_ativo == 'gastos_altos') ? 'active' : ''; ?>">
            Gastaram +R$<?php echo $gastos_altos_valor; ?> (<?php echo $gastos_altos_dias; ?>d)
        </a>
        <a href="base_clientes_vendedor.php?filtro=inativos" class="<?php echo ($filtro_ativo == 'inativos') ? 'active' : ''; ?>">
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
                    <tr><td colspan="5" style="text-align: center; opacity: 0.7;">Nenhum cliente encontrado para este filtro.</td></tr>
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