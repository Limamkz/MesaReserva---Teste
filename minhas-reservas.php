<?php declare(strict_types=1); require_once __DIR__.'/includes/functions.php';
$pdo->exec("UPDATE reservas SET status='expirada' WHERE status IN ('pendente','confirmada') AND TIMESTAMP(data_reserva, hora_reserva) < NOW();"); require_login(); if(is_admin()) redirect('dashboard.php'); $cid=current_client_id();$s=$pdo->prepare("SELECT r.*,m.numero mesa_numero,m.capacidade FROM reservas r INNER JOIN mesas m ON m.id=r.mesa_id WHERE r.cliente_id=? ORDER BY r.data_reserva DESC,r.hora_reserva DESC");$s->execute([$cid]);$reservas=$s->fetchAll();$pageTitle='Minhas reservas';require __DIR__.'/partials/client-header.php'; ?>
<div class="page-heading">
    <div>
        <span class="section-kicker">MEUS MOMENTOS</span>
        <h2>Minhas reservas</h2>
        <p>Acompanhe suas solicitações e horários.</p>
    </div>
    <a class="btn btn-primary" href="<?= url('reservar.php') ?>">Nova reserva</a>
</div>
<section class="card">
    <div class="data-table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Horário</th>
                    <th>Mesa</th>
                    <th>Pessoas</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($reservas as $r): ?>
                <tr>
                    <td>
                        <?= date('d/m/Y',strtotime($r['data_reserva'])) ?>
                    </td>
                    <td>
                        <?= date('H:i',strtotime($r['hora_reserva'])) ?>
                    </td>
                    <td>Mesa <?= e($r['mesa_numero']) ?>
                    </td>
                    <td>
                        <?= (int)$r['pessoas'] ?>
                    </td>
                    <td>
                        <span class="status-badge <?= status_class($r['status']) ?>">
                            <?= status_label($r['status']) ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if(!$reservas): ?>
    <div class="empty-state">
        <span class="material-symbols-outlined">event_busy</span>
        <p>Nenhuma reserva cadastrada.</p>
    </div>
    <?php endif; ?>
</section>
<?php require __DIR__.'/partials/footer.php'; ?>
