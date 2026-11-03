<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$pageTitle = 'Administradores';
$admins = $pdo->query(
    "SELECT id, nome, email, ativo, email_verificado_em, created_at
     FROM usuarios
     WHERE tipo = 'admin'
     ORDER BY nome"
)->fetchAll();

require __DIR__ . '/../partials/header.php';
?>
<div class="page-heading">
    <div>
        <span class="section-kicker">USUÁRIOS</span>
        <h2>Administradores</h2>
        <p>Veja quais administradores possuem acesso à gestão do MesaReserva.</p>
    </div>
    <div class="availability-card small">
        <strong><?= count($admins) ?></strong>
        <span>administradores cadastrados</span>
    </div>
</div>

<section class="card">
    <div class="card-header">
        <div>
            <h3>Equipe administrativa</h3>
            <span>Contas com acesso ao painel de gestão</span>
        </div>
    </div>
    <div class="data-table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Administrador</th>
                    <th>E-mail</th>
                    <th>Status</th>
                    <th>Verificação</th>
                    <th>Cadastro</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($admins as $admin): ?>
                    <tr>
                        <td>
                            <div class="cell-title"><?= e($admin['nome']) ?></div>
                            <div class="cell-sub">ID #<?= (int)$admin['id'] ?></div>
                        </td>
                        <td><?= e($admin['email']) ?></td>
                        <td>
                            <span class="status-badge <?= (int)$admin['ativo'] === 1 ? 'status-confirmada' : 'status-cancelada' ?>">
                                <?= (int)$admin['ativo'] === 1 ? 'Ativo' : 'Inativo' ?>
                            </span>
                        </td>
                        <td>
                            <?= $admin['email_verificado_em'] ? 'Verificado' : 'Pendente' ?>
                        </td>
                        <td><?= date('d/m/Y', strtotime($admin['created_at'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if (!$admins): ?>
        <div class="empty-state">
            <span class="material-symbols-outlined">admin_panel_settings</span>
            <p>Nenhum administrador cadastrado.</p>
        </div>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/../partials/footer.php'; ?>
