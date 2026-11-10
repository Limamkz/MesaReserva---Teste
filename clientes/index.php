<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$pageTitle = 'Clientes';
$clientes = $pdo->query(
    "SELECT c.id, c.nome, c.telefone, c.email, c.created_at,
            IF(c.senha IS NOT NULL, c.id, NULL) AS usuario_id, c.ativo, IF(c.senha IS NOT NULL, 'cliente', NULL) AS tipo,
            (SELECT COUNT(*) FROM reservas r WHERE r.cliente_id = c.id) AS reservas_count
     FROM clientes c
     
     ORDER BY c.nome"
)->fetchAll();
require __DIR__ . '/../partials/header.php';
?>
<div class="page-heading">
    <div>
        <h2>Clientes</h2>
        <p>Consulte os dados de contato e gerencie o acesso às contas.</p>
    </div>
</div>
<section class="card">
    <div class="data-table-wrap">
        <table class="data-table">
            <thead><tr><th>Cliente</th><th>Contato</th><th>Reservas</th><th>Conta</th><th>Ações</th></tr></thead>
            <tbody>
            <?php foreach ($clientes as $c): ?>
                <tr>
                    <td>
                        <div class="client-name-line">
                            <span class="cell-title"><?= e($c['nome']) ?></span>
                            <?php if ($c['usuario_id']): ?>
                                <span class="status-badge client-status-badge <?= (int)$c['ativo'] ? 'status-disponivel' : 'status-cancelada' ?>"><?= (int)$c['ativo'] ? 'Ativa' : 'Inativa' ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="cell-sub">ID #<?= (int)$c['id'] ?></div>
                    </td>
                    <td><div><?= e($c['email'] ?: 'Sem e-mail') ?></div><div class="cell-sub"><?= e($c['telefone'] ?: 'Sem telefone') ?></div></td>
                    <td><?= (int)$c['reservas_count'] ?></td>
                    <td>
                        <?php if ($c['usuario_id']): ?>
                            <form class="client-status-form" method="post" action="<?= url('clientes/status.php') ?>" style="margin-top:5px">
                                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                <input type="hidden" name="usuario_id" value="<?= (int)$c['usuario_id'] ?>">
                                <select class="form-control client-status-select" name="ativo" onchange="this.form.submit()" aria-label="Alterar estado da conta">
                                    <option value="1" <?= (int)$c['ativo'] ? 'selected' : '' ?>>Ativa</option>
                                    <option value="0" <?= !(int)$c['ativo'] ? 'selected' : '' ?>>Inativa</option>
                                </select>
                            </form>
                        <?php else: ?><span class="cell-sub">Sem conta vinculada</span><?php endif; ?>
                    </td>
                    <td><div class="row-actions client-delete-actions">
                        <?php if ($c['usuario_id'] && ($c['tipo'] ?? '') === 'cliente'): ?>
                            <button type="button" class="btn btn-light btn-sm promote-admin-trigger" data-client-id="<?= (int)$c['usuario_id'] ?>" data-client-name="<?= e($c['nome']) ?>">
                                <span class="material-symbols-outlined">admin_panel_settings</span>Tornar administrador
                            </button>
                        <?php endif; ?>
                        <a href="<?= url('clientes/delete.php?id=' . (int)$c['id']) ?>" class="btn btn-light btn-sm reservation-delete-btn" data-confirm="Tem certeza que deseja excluir este cliente? Esta ação não pode ser desfeita." aria-label="Excluir cliente <?= (int)$c['id'] ?>">
                            <span class="material-symbols-outlined">delete</span>Excluir
                        </a>
                    </div></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if (!$clientes): ?><div class="empty-state"><span class="material-symbols-outlined">groups</span><p>Nenhum cliente cadastrado.</p></div><?php endif; ?>
</section>
<div class="admin-key-modal" id="adminKeyModal" aria-hidden="true">
    <div class="admin-key-backdrop" data-close-admin-modal></div>
    <section class="admin-key-dialog" role="dialog" aria-modal="true" aria-labelledby="adminKeyTitle">
        <button type="button" class="admin-key-close" aria-label="Fechar" data-close-admin-modal>&times;</button>
        <img src="<?= url('assets/img/logo.png') ?>" alt="MesaReserva" class="admin-key-logo">
        <span class="admin-key-kicker">PERMISSÃO ADMINISTRATIVA</span>
        <h3 id="adminKeyTitle">Tornar administrador</h3>
        <p id="adminKeyDescription">Ao confirmar, a conta será movida de clientes para administradores. Reservas, perguntas e avaliações vinculadas ao cadastro de cliente serão removidas. Digite a chave administrativa para continuar.</p>
        <form method="post" action="<?= url('clientes/promover-admin.php') ?>" class="admin-key-form">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="cliente_id" id="adminModalClientId" value="">
            <label for="adminModalKey">Senha administrativa</label>
            <input class="form-control" type="password" name="chave_admin" id="adminModalKey" placeholder="Digite a chave administrativa" autocomplete="current-password" required>
            <div class="admin-key-actions">
                <button type="button" class="btn btn-light" data-close-admin-modal>Cancelar</button>
                <button type="submit" class="btn btn-primary">Confirmar</button>
            </div>
        </form>
    </section>
</div>
<script>
(() => {
    const modal = document.getElementById('adminKeyModal');
    const idField = document.getElementById('adminModalClientId');
    const keyField = document.getElementById('adminModalKey');
    const description = document.getElementById('adminKeyDescription');
    const close = () => { modal.classList.remove('is-open'); modal.setAttribute('aria-hidden', 'true'); document.body.classList.remove('admin-modal-open'); };
    document.querySelectorAll('.promote-admin-trigger').forEach(button => button.addEventListener('click', () => {
        idField.value = button.dataset.clientId;
        description.textContent = `Ao confirmar, ${button.dataset.clientName} será movido de clientes para administradores. Os registros de reservas, perguntas e avaliações vinculados ao cadastro de cliente serão removidos. Digite a chave administrativa para continuar.`;
        modal.classList.add('is-open'); modal.setAttribute('aria-hidden', 'false'); document.body.classList.add('admin-modal-open');
        window.setTimeout(() => keyField.focus(), 80);
    }));
    modal.querySelectorAll('[data-close-admin-modal]').forEach(button => button.addEventListener('click', close));
    document.addEventListener('keydown', event => { if (event.key === 'Escape' && modal.classList.contains('is-open')) close(); });
})();
</script>
<style>
/* Lista de clientes: status discreto ao lado do nome e ações alinhadas. */
.client-name-line { display: flex; align-items: center; flex-wrap: wrap; gap: 7px; }
.client-name-line .cell-title { display: inline-block; }
.client-status-badge { display: inline-flex; align-items: center; min-height: 19px; padding: 2px 7px; border-radius: 999px; font-size: 10px; line-height: 1.2; font-weight: 700; letter-spacing: .01em; }
.client-status-badge::before { width: 5px; height: 5px; margin-right: 4px; }
.client-status-form { margin-top: 5px !important; }
.client-status-select { width: 112px; min-width: 112px; min-height: 32px; height: 32px; padding: 5px 25px 5px 9px; border-radius: 8px; font-size: 12px; line-height: 1.2; }
.promote-admin-form { display:flex; flex-wrap:wrap; gap:6px; margin-right:8px; align-items:center; } .promote-admin-form input { width:150px; min-height:32px; font-size:11px; }
.client-delete-actions { display: flex; justify-content: flex-start; align-items: center; width: 100%; padding-left: 0; }
.client-delete-actions .reservation-delete-btn { display: inline-flex; align-items: center; justify-content: center; gap: 5px; min-height: 32px; padding: 6px 10px; margin: 0; color: #b42318 !important; background: #fff !important; border: 1px solid #f0c7c4 !important; border-radius: 8px; font-weight: 700; white-space: nowrap; text-align: center; }
.client-delete-actions .reservation-delete-btn:hover { background: #fff0ef !important; border-color: #e8b4b0 !important; }
.client-delete-actions .reservation-delete-btn .material-symbols-outlined { font-size: 18px; flex-shrink: 0; }
.reservation-delete-btn .material-symbols-outlined { font-size: 18px; }

.client-delete-actions { gap:8px; flex-wrap:nowrap; }
.client-delete-actions > * { flex:0 0 auto; }
.promote-admin-trigger { display:inline-flex; align-items:center; justify-content:center; gap:5px; min-height:32px; padding:6px 10px; white-space:nowrap; }
.admin-key-modal { position:fixed; inset:0; z-index:9999; display:none; align-items:center; justify-content:center; padding:20px; }
.admin-key-modal.is-open { display:flex; }
.admin-key-backdrop { position:absolute; inset:0; background:rgba(24,19,14,.62); backdrop-filter:blur(5px); }
.admin-key-dialog { position:relative; z-index:1; width:min(440px,100%); padding:34px 34px 30px; border:1px solid #eee2d2; border-radius:24px; background:#fffdfa; box-shadow:0 28px 90px rgba(0,0,0,.28); text-align:center; animation:adminDialogIn .18s ease-out; }
.admin-key-logo { display:block; width:auto; max-width:190px; height:72px; object-fit:contain; margin:0 auto 15px; }
.admin-key-kicker { color:#b8793f; font-size:10px; font-weight:800; letter-spacing:.16em; }
.admin-key-dialog h3 { margin:8px 0; color:#30251b; font-size:23px; }
.admin-key-dialog p { margin:0 auto 22px; max-width:340px; color:#75695d; font-size:13px; line-height:1.6; }
.admin-key-close { position:absolute; top:12px; right:15px; border:0; background:transparent; color:#8a7d70; font-size:28px; cursor:pointer; }
.admin-key-form { text-align:left; }
.admin-key-form label { display:block; margin-bottom:7px; color:#514438; font-size:12px; font-weight:700; }
.admin-key-form .form-control { min-height:46px; border-radius:11px; }
.admin-key-actions { display:flex; justify-content:flex-end; gap:9px; margin-top:20px; }
body.admin-modal-open { overflow:hidden; }
@keyframes adminDialogIn { from { opacity:0; transform:translateY(8px) scale(.98); } to { opacity:1; transform:translateY(0) scale(1); } }
@media(max-width:700px) { .client-delete-actions { flex-wrap:wrap; } .admin-key-dialog { padding:30px 22px 24px; } }
</style>
<?php require __DIR__ . '/../partials/footer.php'; ?>
