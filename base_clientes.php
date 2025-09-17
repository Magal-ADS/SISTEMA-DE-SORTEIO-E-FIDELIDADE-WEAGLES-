<?php
// /base_clientes.php (VERSÃO COM BARRA DE PESQUISA)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['cargo']) || $_SESSION['cargo'] != 1) {
    header("Location: login.php");
    exit();
}

include 'templates/header.php';
require_once 'php/db_config.php';

// --- Lógica PHP para buscar os dados (inalterada) ---
$configs = [];
$result_configs = pg_query($link, "SELECT chave, valor FROM configuracoes WHERE chave LIKE 'filtro_%'");
if ($result_configs) { while($row = pg_fetch_assoc($result_configs)){ $configs[$row['chave']] = $row['valor']; } }
$inativos_meses = $configs['filtro_inativos_meses'] ?? 6;
$gastos_altos_valor = $configs['filtro_gastos_altos_valor'] ?? 1000;
$gastos_altos_dias = $configs['filtro_gastos_altos_dias'] ?? 90;
$filtro_ativo = $_GET['filtro'] ?? 'todos';
$clientes = [];
$admin_id = $_SESSION['usuario_id'];
$sql = '';
$params = [];
$query_name = 'admin_filtro_' . $filtro_ativo . '_v2';
switch ($filtro_ativo) {
    case 'aniversariantes_dia':
        $sql = "SELECT nome_completo, whatsapp, data_nascimento, data_cadastro FROM clientes WHERE usuario_id = $1 AND EXTRACT(DAY FROM data_nascimento) = EXTRACT(DAY FROM CURRENT_DATE) AND EXTRACT(MONTH FROM data_nascimento) = EXTRACT(MONTH FROM CURRENT_DATE)";
        $params = [$admin_id];
        break;
    case 'inativos':
        $sql = "SELECT c.nome_completo, c.whatsapp, c.data_nascimento, c.data_cadastro FROM clientes c LEFT JOIN ( SELECT cliente_id, MAX(data_compra) as ultima_compra FROM compras GROUP BY cliente_id ) AS ultimas_compras ON c.id = ultimas_compras.cliente_id WHERE c.usuario_id = $1 AND (ultimas_compras.ultima_compra IS NULL OR ultimas_compras.ultima_compra < (CURRENT_DATE - ($2 || ' months')::interval)) ORDER BY c.nome_completo ASC";
        $params = [$admin_id, $inativos_meses];
        break;
    case 'gastos_altos':
        $sql = "SELECT c.nome_completo, c.whatsapp, c.data_nascimento, c.data_cadastro, SUM(co.valor) AS total_gasto FROM clientes c JOIN compras co ON c.id = co.cliente_id WHERE c.usuario_id = $1 AND co.data_compra >= (CURRENT_DATE - ($2 || ' days')::interval) GROUP BY c.id HAVING SUM(co.valor) >= $3 ORDER BY total_gasto DESC";
        $params = [$admin_id, $gastos_altos_dias, $gastos_altos_valor];
        break;
    default:
        $sql = "SELECT nome_completo, whatsapp, data_nascimento, data_cadastro FROM clientes WHERE usuario_id = $1 ORDER BY data_cadastro DESC";
        $params = [$admin_id];
        break;
}
$stmt = pg_prepare($link, $query_name, $sql);
if ($stmt) {
    $result = pg_execute($link, $query_name, $params);
    if ($result) { while ($row = pg_fetch_assoc($result)) { $clientes[] = $row; } }
}
pg_close($link);
?>

<title>Base de Clientes</title>

<style>
    /* Estilos antigos (inalterados) */
    .page-header h1 { color: var(--cor-dourado) !important; }
    .page-header p { color: var(--cor-branco) !important; opacity: 0.8; }
    .filter-nav a { background-color: rgba(255, 255, 255, 0.1); color: var(--cor-branco); border-color: rgba(255, 255, 255, 0.2); }
    .filter-nav a:hover { background-color: rgba(255, 255, 255, 0.2); border-color: var(--cor-dourado); }
    .filter-nav a.active { background: var(--cor-dourado) !important; color: var(--cor-texto-principal) !important; }
    .table-wrapper { background-color: rgba(44, 44, 44, 0.5); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.1); width: 100%; overflow-x: auto; }
    .data-table { width: 100%; border-collapse: collapse; }
    .data-table th, .data-table td { color: var(--cor-branco) !important; border-bottom: 1px solid rgba(255, 255, 255, 0.1); }
    .data-table th { opacity: 0.9; }
    .data-table td { opacity: 0.7; }
    
    /* =================== NOVO CSS PARA A BARRA DE PESQUISA =================== */
    .search-container {
        margin-bottom: 1.5rem;
    }
    #searchInput {
        width: 100%;
        padding: 12px 20px;
        background-color: rgba(0, 0, 0, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 50px;
        color: var(--cor-branco);
        font-size: 1rem;
        outline: none;
        transition: border-color 0.3s ease;
        box-sizing: border-box;
    }
    #searchInput::placeholder {
        color: rgba(255, 255, 255, 0.5);
    }
    #searchInput:focus {
        border-color: var(--cor-dourado);
    }
    /* ======================================================================= */
</style>
<div class="page-container">
    <header class="page-header">
        <h1>Base de Clientes</h1>
        <p>Visualize e filtre todos os clientes cadastrados em sua loja.</p>
    </header>

    <div class="search-container">
        <input type="text" id="searchInput" placeholder="Pesquisar por nome do cliente...">
    </div>
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
                    <tr id="linha-sem-dados"><td colspan="5" style="text-align:center;">Nenhum cliente encontrado para este filtro.</td></tr>
                <?php else: ?>
                    <?php foreach ($clientes as $cliente): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($cliente['nome_completo']); ?></td>
                            <td><?php echo htmlspecialchars($cliente['whatsapp'] ?? '--'); ?></td>
                            <td><?php echo !empty($cliente['data_nascimento']) ? date('d/m', strtotime($cliente['data_nascimento'])) : '--'; ?></td>
                            <td><?php echo !empty($cliente['data_cadastro']) ? date('d/m/Y', strtotime($cliente['data_cadastro'])) : '--'; ?></td>
                            <?php if ($filtro_ativo == 'gastos_altos'): ?>
                                <td><?php echo 'R$ ' . number_format($cliente['total_gasto'], 2, ',', '.'); ?></td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                <tr id="linha-sem-resultado-busca" style="display: none;"><td colspan="5" style="text-align:center;">Nenhum cliente encontrado com este nome.</td></tr>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const tableBody = document.querySelector('.data-table tbody');
    const allRows = Array.from(tableBody.querySelectorAll('tr:not(#linha-sem-resultado-busca)'));
    const noFilterResultsRow = document.getElementById('linha-sem-dados');
    const noSearchResultsRow = document.getElementById('linha-sem-resultado-busca');

    searchInput.addEventListener('keyup', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        let visibleRows = 0;

        // Esconde a mensagem de "sem resultado de filtro" se ela existir
        if (noFilterResultsRow) noFilterResultsRow.style.display = 'none';

        allRows.forEach(row => {
            const clientNameCell = row.querySelector('td:first-child');
            if (clientNameCell) {
                const clientName = clientNameCell.textContent.toLowerCase();
                if (clientName.includes(searchTerm)) {
                    row.style.display = '';
                    visibleRows++;
                } else {
                    row.style.display = 'none';
                }
            }
        });

        // Controla a exibição da mensagem "Nenhum cliente encontrado com este nome"
        if (visibleRows === 0 && !noFilterResultsRow) {
            noSearchResultsRow.style.display = 'table-row';
        } else {
            noSearchResultsRow.style.display = 'none';
        }
    });
});
</script>
<?php
include 'templates/footer.php';
?>