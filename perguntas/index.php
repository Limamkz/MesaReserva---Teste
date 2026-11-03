<?php declare(strict_types=1); require_once __DIR__.'/../config/admin_guard.php';$pageTitle='Perguntas e atendimento';$s=$pdo->query("SELECT p.*,u.nome,u.email FROM perguntas p INNER JOIN usuarios u ON u.id=p.usuario_id ORDER BY p.status ASC,p.created_at DESC");$items=$s->fetchAll();require __DIR__.'/../partials/header.php'; ?>
<div class="page-heading">
    <div>
        <h2>Atendimento dos clientes</h2>
        <p>Veja dúvidas, problemas e solicitações enviadas pelos clientes.</p>
    </div>
</div>
<section class="card">
    <div class="data-table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>Assunto</th>
                    <th>Mensagem</th>
                    <th>Status</th>
                    <th>Ação</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($items as $p): ?>
                <tr>
                    <td>
                        <div class="cell-title">
                            <?= e($p['nome']) ?>
                        </div>
                        <div class="cell-sub">
                            <?= e($p['email']) ?>
                        </div>
                    </td>
                    <td>
                        <?= e($p['assunto']) ?>
                    </td>
                    <td>
                        <div class="cell-sub long-text">
                            <?= e($p['mensagem']) ?>
                        </div>
                        <?php if($p['resposta']): ?>
                        <div class="reply-preview">Resposta: <?= e($p['resposta']) ?>
                        </div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="status-badge <?= $p['status']==='respondida'?'status-concluida':'status-pendente' ?>">
                            <?= e(ucfirst($p['status'])) ?>
                        </span>
                    </td>
                    <td>
                        <a class="icon-action" href="<?= url('perguntas/responder.php?id='.$p['id']) ?>">
                            <span class="material-symbols-outlined">reply</span>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php require __DIR__.'/../partials/footer.php'; ?>
