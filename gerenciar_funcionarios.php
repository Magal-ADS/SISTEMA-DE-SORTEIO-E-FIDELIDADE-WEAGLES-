<?php
// /gerenciar_funcionarios.php (VERSÃO FINAL E CORRIGIDA)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['cargo']) || $_SESSION['cargo'] != 1) {
    header("Location: login.php");
    exit();
}

require_once 'php/db_config.php';

$funcionarios = [];
$admin_id = $_SESSION['usuario_id'];

// CORREÇÃO 1: 'CARGO' para 'cargo' (minúsculo) na consulta SQL
$sql = "SELECT id, nome, cpf, cargo FROM usuarios WHERE cargo != 1 AND id != $1";

$stmt = pg_prepare($link, "listar_funcionarios_query", $sql);
if ($stmt) {
    $result = pg_execute($link, "listar_funcionarios_query", [$admin_id]);
    if ($result) {
        while ($row = pg_fetch_assoc($result)) {
            $funcionarios[] = $row;
        }
    }
}
pg_close($link);
include 'templates/header.php';
?>

<title>Gerenciar Funcionários</title>

<style>
    /* Estilos para o tema escuro */
    .page-header h1 { color: var(--cor-dourado) !important; }
    .page-header p { color: var(--cor-branco) !important; opacity: 0.8; }
    .table-wrapper { background-color: rgba(44, 44, 44, 0.5); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.1); }
    .data-table th, .data-table td { color: var(--cor-branco) !important; border-bottom: 1px solid rgba(255, 255, 255, 0.1); }
    .data-table th { opacity: 0.9; }
    .data-table td { opacity: 0.7; }
    .modal-box { background-color: #2c2c2c; }
    .modal-title, .modal-text, .form-group label, .modal-text strong { color: var(--cor-branco); }
    .modal-box .form-group input, .modal-box .form-group select { background-color: rgba(0,0,0,0.2); border-color: rgba(255,255,255,0.2); color: var(--cor-branco); }
    .modal-error { color: #ff8a8a; }
    .modal-actions .btn-light { background-color: #444; color: var(--cor-branco); border: 1px solid #555; }
</style>

<div class="page-container">
    <header class="page-header with-action">
        <div>
            <h1>Gerenciar Funcionários</h1>
            <p>Adicione, edite ou remova funcionários do sistema.</p>
        </div>
        <button id="btn-abrir-popup-adicionar" class="btn btn-verde">Adicionar Funcionário</button>
    </header>

    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>CPF</th>
                    <th>Cargo</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody id="tabela-funcionarios-body">
                <?php if (empty($funcionarios)): ?>
                    <tr id="linha-sem-dados">
                        <td colspan="4">Nenhum funcionário cadastrado.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($funcionarios as $func): ?>
                        <tr data-id-linha="<?php echo $func['id']; ?>">
                            <td><?php echo htmlspecialchars($func['nome']); ?></td>
                            <td><?php echo htmlspecialchars($func['cpf']); ?></td>
                            <td><?php echo ($func['cargo'] == 2) ? 'Vendedor' : 'Outro'; ?></td>
                            <td class="actions-cell">
                                <button class="btn-action edit" data-id="<?php echo $func['id']; ?>">Editar</button>
                                <button class="btn-action delete" data-id="<?php echo $func['id']; ?>" data-nome="<?php echo htmlspecialchars($func['nome']); ?>">Remover</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal-overlay" id="modal-adicionar-funcionario">
    <form id="form-adicionar-funcionario" class="modal-box" action="php/adicionar_funcionario.php" method="POST">
        <h2 class="modal-title">Adicionar Novo Funcionário</h2>
        <div class="form-group">
            <label for="nome">Nome Completo</label>
            <input type="text" id="nome" name="nome" required>
        </div>
        <div class="form-group">
            <label for="cpf-modal">CPF</label>
            <input type="text" id="cpf-modal" name="cpf" placeholder="000.000.000-00" required>
        </div>
        <div class="form-group">
            <label for="senha">Senha de Acesso</label>
            <input type="password" id="senha" name="senha" required>
        </div>
        <div class="form-group">
            <label for="cargo">Nível de Acesso</label>
            <select id="cargo" name="cargo" required>
                <option value="2" selected>Vendedor</option>
                <option value="1">Administrador (Chefe)</option>
            </select>
        </div>
        <p id="modal-adicionar-error-message" class="modal-error"></p>
        <div class="modal-actions">
            <button type="button" class="btn btn-light" id="btn-cancelar-adicao">Cancelar</button>
            <button type="submit" class="btn btn-verde" id="btn-salvar-adicao">Salvar Funcionário</button>
        </div>
    </form>
</div>

<div class="modal-overlay" id="modal-editar-funcionario">
    <form id="form-editar-funcionario" class="modal-box" action="php/editar_funcionario.php" method="POST">
        <h2 class="modal-title">Editar Funcionário</h2>
        <input type="hidden" id="edit-id" name="id">
        <div class="form-group">
            <label for="edit-nome">Nome Completo</label>
            <input type="text" id="edit-nome" name="nome" required>
        </div>
        <div class="form-group">
            <label for="edit-cpf">CPF</label>
            <input type="text" id="edit-cpf" name="cpf" placeholder="000.000.000-00" required>
        </div>
        <div class="form-group">
            <label for="edit-senha">Nova Senha (opcional)</label>
            <input type="password" id="edit-senha" name="senha" placeholder="Deixe em branco para não alterar">
        </div>
        <div class="form-group">
            <label for="edit-cargo">Nível de Acesso</label>
            <select id="edit-cargo" name="cargo" required>
                <option value="2">Vendedor</option>
                <option value="1">Administrador (Chefe)</option>
            </select>
        </div>
        <p id="modal-editar-error-message" class="modal-error"></p>
        <div class="modal-actions">
            <button type="button" class="btn btn-light" id="btn-cancelar-edicao">Cancelar</button>
            <button type="submit" class="btn btn-verde" id="btn-salvar-edicao">Salvar Alterações</button>
        </div>
    </form>
</div>

<div class="modal-overlay" id="modal-confirmar-remocao">
    <form id="form-confirmar-remocao" action="php/remover_funcionario.php" method="POST" class="modal-box">
        <h2 class="modal-title">Confirmar Remoção</h2>
        <p class="modal-text">Para remover o funcionário <strong id="nome-funcionario-para-remover"></strong>, por favor, digite sua senha de administrador.</p>
        <input type="hidden" id="id-funcionario-para-remover" name="id">
        <div class="form-group">
            <label for="senha_admin">Sua Senha</label>
            <input type="password" id="senha_admin" name="senha_admin" required>
            <p id="modal-remover-error-message" class="modal-error"></p>
        </div>
        <div class="modal-actions">
            <button type="button" class="btn btn-light" id="btn-cancelar-remocao">Cancelar</button>
            <button type="submit" class="btn btn-action delete" id="btn-confirmar-remocao">Confirmar Remoção</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabelaBody = document.getElementById('tabela-funcionarios-body');
    const modalAdicionar = document.getElementById('modal-adicionar-funcionario');
    const formAdicionar = document.getElementById('form-adicionar-funcionario');
    const btnAbrirPopupAdicionar = document.getElementById('btn-abrir-popup-adicionar');
    const btnCancelarAdicao = document.getElementById('btn-cancelar-adicao');
    const erroAdicionarMsg = document.getElementById('modal-adicionar-error-message');

    if (btnAbrirPopupAdicionar) {
        btnAbrirPopupAdicionar.addEventListener('click', () => {
            formAdicionar.reset();
            erroAdicionarMsg.textContent = '';
            modalAdicionar.classList.add('visible');
        });
    }
    const closeModalAdicionar = () => modalAdicionar.classList.remove('visible');
    if(btnCancelarAdicao) btnCancelarAdicao.addEventListener('click', closeModalAdicionar);
    if(modalAdicionar) modalAdicionar.addEventListener('click', e => { if (e.target === modalAdicionar) closeModalAdicionar(); });
    
    if (formAdicionar) {
        formAdicionar.addEventListener('submit', function(e) {
            e.preventDefault();
            const btnSalvar = document.getElementById('btn-salvar-adicao');
            btnSalvar.disabled = true;
            btnSalvar.textContent = 'Salvando...';
            erroAdicionarMsg.textContent = '';
            const formData = new FormData(formAdicionar);
            fetch(formAdicionar.action, { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    closeModalAdicionar();
                    const novoFunc = data.novoFuncionario;
                    const newRow = tabelaBody.insertRow(0);
                    newRow.dataset.idLinha = novoFunc.id;
                    newRow.innerHTML = `
                        <td>${novoFunc.nome}</td>
                        <td>${novoFunc.cpf}</td>
                        <td>${novoFunc.cargo}</td>
                        <td class="actions-cell">
                            <button class="btn-action edit" data-id="${novoFunc.id}">Editar</button>
                            <button class="btn-action delete" data-id="${novoFunc.id}" data-nome="${novoFunc.nome}">Remover</button>
                        </td>
                    `;
                    const noDataRow = document.getElementById('linha-sem-dados');
                    if (noDataRow) noDataRow.remove();
                } else {
                    erroAdicionarMsg.textContent = data.message;
                }
            })
            .finally(() => {
                btnSalvar.disabled = false;
                btnSalvar.textContent = 'Salvar Funcionário';
            });
        });
    }

    const modalRemover = document.getElementById('modal-confirmar-remocao');
    const formRemover = document.getElementById('form-confirmar-remocao');
    const btnCancelarRemocao = document.getElementById('btn-cancelar-remocao');
    const nomeFuncionarioSpan = document.getElementById('nome-funcionario-para-remover');
    const idFuncionarioInput = document.getElementById('id-funcionario-para-remover');
    const senhaAdminInput = document.getElementById('senha_admin');
    const erroRemoverMsg = document.getElementById('modal-remover-error-message');
    const modalEditar = document.getElementById('modal-editar-funcionario');
    const formEditar = document.getElementById('form-editar-funcionario');
    const btnCancelarEdicao = document.getElementById('btn-cancelar-edicao');
    const erroEditarMsg = document.getElementById('modal-editar-error-message');

    tabelaBody.addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('delete')) {
            const button = e.target;
            nomeFuncionarioSpan.textContent = `"${button.dataset.nome}"`;
            idFuncionarioInput.value = button.dataset.id;
            senhaAdminInput.value = '';
            erroRemoverMsg.textContent = '';
            modalRemover.classList.add('visible');
            senhaAdminInput.focus();
        }
        if (e.target && e.target.classList.contains('edit')) {
            const id = e.target.dataset.id;
            erroEditarMsg.textContent = '';
            fetch(`php/get_funcionario.php?id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    const func = data.funcionario;
                    document.getElementById('edit-id').value = id;
                    document.getElementById('edit-nome').value = func.nome;
                    document.getElementById('edit-cpf').value = func.cpf;
                    document.getElementById('edit-cargo').value = func.cargo; // <-- Corrigido aqui também
                    document.getElementById('edit-senha').value = '';
                    modalEditar.classList.add('visible');
                } else {
                    alert('Erro: ' + data.message);
                }
            });
        }
    });

    const closeModalRemover = () => modalRemover.classList.remove('visible');
    if (btnCancelarRemocao) btnCancelarRemocao.addEventListener('click', closeModalRemover);
    if (modalRemover) modalRemover.addEventListener('click', e => { if (e.target === modalRemover) closeModalRemover(); });

    const closeModalEditar = () => modalEditar.classList.remove('visible');
    if (btnCancelarEdicao) btnCancelarEdicao.addEventListener('click', closeModalEditar);
    if (modalEditar) modalEditar.addEventListener('click', e => { if (e.target === modalEditar) closeModalEditar(); });

    if (formRemover) {
        formRemover.addEventListener('submit', function(e) {
            e.preventDefault();
            const btnConfirmar = document.getElementById('btn-confirmar-remocao');
            btnConfirmar.disabled = true;
            btnConfirmar.textContent = 'Removendo...';
            erroRemoverMsg.textContent = '';
            const formData = new FormData(formRemover);
            fetch(formRemover.action, { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    closeModalRemover();
                    const linhaParaRemover = document.querySelector(`tr[data-id-linha="${formData.get('id')}"]`);
                    if (linhaParaRemover) {
                        linhaParaRemover.style.opacity = '0';
                        setTimeout(() => linhaParaRemover.remove(), 300);
                    }
                } else {
                    erroRemoverMsg.textContent = data.message;
                }
            })
            .finally(() => {
                btnConfirmar.disabled = false;
                btnConfirmar.textContent = 'Confirmar Remoção';
            });
        });
    }
    if (formEditar) {
        formEditar.addEventListener('submit', function(e) {
            e.preventDefault();
            const btnSalvarEdicao = document.getElementById('btn-salvar-edicao');
            btnSalvarEdicao.disabled = true;
            btnSalvarEdicao.textContent = 'Salvando...';
            erroEditarMsg.textContent = '';
            const formData = new FormData(formEditar);
            fetch(formEditar.action, { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    closeModalEditar();
                    const func = data.funcionarioAtualizado;
                    const linha = document.querySelector(`tr[data-id-linha="${func.id}"]`);
                    if (linha) {
                        linha.children[0].textContent = func.nome;
                        linha.children[1].textContent = func.cpf;
                        linha.children[2].textContent = func.cargo;
                    }
                } else {
                    erroEditarMsg.textContent = data.message;
                }
            })
            .finally(() => {
                btnSalvarEdicao.disabled = false;
                btnSalvarEdicao.textContent = 'Salvar Alterações';
            });
        });
    }
});
</script>

<?php include 'templates/footer.php'; ?>