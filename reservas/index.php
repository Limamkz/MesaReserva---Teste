<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$pageTitle='Reservas';

// Reservas não concluídas que passaram do horário deixam de ficar pendentes/confirmadas.
$pdo->exec("UPDATE reservas SET status='expirada'
            WHERE status IN ('pendente','confirmada')
            AND TIMESTAMP(data_reserva, hora_reserva) < NOW();");
$filterDate=$_GET['data']??'';
$where='';$params=[];
if($filterDate){$where='WHERE r.data_reserva = ?';$params[]=$filterDate;}
$s=$pdo->prepare(
"SELECT r.*,c.nome cliente_nome,c.telefone,m.numero mesa_numero,m.capacidade
FROM reservas r INNER JOIN clientes c ON c.id=r.cliente_id INNER JOIN mesas m ON m.id=r.mesa_id
$where ORDER BY r.data_reserva DESC,r.hora_reserva ASC"
);$s->execute($params);$reservas=$s->fetchAll();
require __DIR__.'/../partials/header.php';
?>
<div class="page-heading">
    <div>
        <h2>Reservas</h2>
        <p>Organize clientes, mesas, horários e situação de cada reserva.</p>
    </div>
    <a class="btn btn-primary" href="<?= url('reservas/form.php') ?>">
        <span class="material-symbols-outlined">calendar_add_on</span>Nova reserva</a>
        </div>
        <section class="card">
            <form class="filter-bar" method="get">
                <input class="form-control" type="date" name="data" value="<?= e($filterDate) ?>">
                <button class="btn btn-light" type="submit">
                    <span class="material-symbols-outlined">filter_alt</span>Filtrar</button>
                        <?php if($filterDate): ?>
                        <a class="btn btn-light" href="<?= url('reservas/index.php') ?>">Limpar</a>
                        <?php endif; ?>
                    </form>
                    <div class="data-table-wrap">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Data / hora</th>
                                    <th>Cliente</th>
                                    <th>Mesa</th>
                                    <th>Pessoas</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($reservas as $r): ?>
                                <tr>
                                    <td>
                                        <div class="cell-title">
                                            <?= date('d/m/Y',strtotime($r['data_reserva'])) ?>
                                        </div>
                                        <div class="cell-sub">
                                            <?= date('H:i',strtotime($r['hora_reserva'])) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="cell-title">
                                            <?= e($r['cliente_nome']) ?>
                                        </div>
                                        <div class="cell-sub">
                                            <?= e($r['telefone'] ?: 'Sem telefone') ?>
                                        </div>
                                    </td>
                                    <td>Mesa <?= e($r['mesa_numero']) ?>
                                        <div class="cell-sub">até <?= (int)$r['capacidade'] ?> pessoas</div>
                                    </td>
                                    <td>
                                        <?= (int)$r['pessoas'] ?>
                                    </td>
                                    <td>
                                        <?php if ($r['status'] === 'expirada'): ?>
                                            <span class="status-badge status-expirada reservation-expired-label">Expirado</span>
                                        <?php else: ?>
                                            <form class="reservation-status-form" method="post" action="<?= url('reservas/atualizar-status.php') ?>">
                                                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                                <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                                                <select class="form-control reservation-status-select <?= e(status_class($r['status'])) ?>"
                                                        name="status" aria-label="Alterar status da reserva"
                                                        onchange="this.form.submit()"
                                                        <?= in_array($r['status'], ['cancelada','concluida'], true) ? 'disabled' : '' ?>>
                                                    <option value="pendente" <?= $r['status']==='pendente'?'selected':'' ?>>Pendente</option>
                                                    <option value="confirmada" <?= $r['status']==='confirmada'?'selected':'' ?>><?= $r['status']==='confirmada'?'Confirmada':'Confirmar reserva' ?></option>
                                                    <option value="cancelada" <?= $r['status']==='cancelada'?'selected':'' ?>><?= $r['status']==='cancelada'?'Recusada':'Recusar reserva' ?></option>
                                                    <option value="concluida" <?= $r['status']==='concluida'?'selected':'' ?>><?= $r['status']==='concluida'?'Concluída':'Marcar como concluída' ?></option>
                                                </select>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <form method="post" action="<?= url('reservas/delete.php') ?>" onsubmit="return confirm('Tem certeza que deseja excluir esta reserva? Esta ação não pode ser desfeita.');">
                                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                            <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                                            <button type="submit" class="btn btn-light btn-sm reservation-delete-btn" aria-label="Excluir reserva <?= (int)$r['id'] ?>">
                                                <span class="material-symbols-outlined">delete</span>Excluir
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if(!$reservas): ?>
                    <div class="empty-state">
                        <span class="material-symbols-outlined">event_busy</span>
                        <p>Nenhuma reserva encontrada.</p>
                    </div>
                    <?php endif; ?>
                </section>
                <style>
.status-expirada { color:#7b4d18; background:#f8ead1; }
.reservation-status-form { min-width: 175px; }
.reservation-delete-btn { display:inline-flex; align-items:center; justify-content:center; gap:5px; min-height:34px; padding:7px 12px; color:#b42318 !important; background:#fff !important; border:1px solid #f0c7c4 !important; border-radius:8px; font-weight:700; white-space:nowrap; }
.reservation-delete-btn:hover { background:#fff0ef !important; border-color:#e8b4b0 !important; }
.reservation-delete-btn .material-symbols-outlined { font-size:18px; }
.reservation-status-select { width: 148px; min-width: 148px; padding: 6px 25px 6px 8px; font-size: 12px; }
.reservation-status-select:disabled { opacity: .8; cursor: not-allowed; }
.reservation-expired-label { display: inline-flex; align-items: center; min-height: 36px; white-space: nowrap; }
</style>
<?php require __DIR__.'/../partials/footer.php'; ?>
