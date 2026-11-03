<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$pageTitle = 'Mesas';

$mesas = $pdo->query(
"SELECT m.*,
 CASE
   WHEN m.status = 'ocupada' THEN 'ocupada'
   WHEN EXISTS (
      SELECT 1 FROM reservas r
      WHERE r.mesa_id = m.id AND r.data_reserva = CURDATE()
        AND r.status = 'confirmada' AND TIMESTAMP(r.data_reserva, r.hora_reserva) >= NOW()
   ) THEN 'reservada'
   ELSE 'disponivel'
 END AS status_dia,
 COALESCE((SELECT SUM(c.quantidade * c.valor_unitario)
           FROM consumos c
           WHERE c.mesa_id = m.id AND DATE(c.created_at) = CURDATE()), 0) AS total_gasto
FROM mesas m
ORDER BY m.numero"
)->fetchAll();

require __DIR__ . '/../partials/header.php';
?>
<div class="page-heading">
    <div>
        <h2>Mesas do restaurante</h2>
        <p>O status e o consumo exibidos correspondem ao dia de hoje.</p>
    </div>
    <div class="actions">
        <a class="btn btn-primary" href="<?= url('mesas/form.php') ?>">
            <span class="material-symbols-outlined">add</span>Nova mesa</a>
            </div>
        </div>
        <section class="card">
            <div class="card-header">
                <div>
                    <h3>Mapa operacional</h3>
                    <span>
                        <?= count($mesas) ?> mesa(s) cadastrada(s)</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="legend">
                        <span>
                            <i class="green">
                            </i>Disponível</span>
                            <span>
                                <i class="yellow">
                                </i>Reservada</span>
                                <span>
                                    <i class="red">
                                    </i>Ocupada</span>
                                </div>
                                <div class="table-map">
                                    <?php foreach ($mesas as $mesa): ?>
                                    <div class="table-card <?= e($mesa['status_dia']) ?>">
                                        <div class="table-top">
                                            <div>
                                                <div class="table-number">Mesa <?= e($mesa['numero']) ?>
                                                </div>
                                                <div class="capacity">
                                                    <?= (int)$mesa['capacidade'] ?> lugares</div>
                                                </div>
                                                <span class="status-badge <?= status_class($mesa['status_dia']) ?>">
                                                    <?= status_label($mesa['status_dia']) ?>
                                                </span>
                                            </div>
                                            <div class="table-total">Consumo de hoje<strong>
                                                <?= money($mesa['total_gasto']) ?>
                                            </strong>
                                        </div>
                                        <div class="row-actions" style="justify-content:flex-start;margin-top:9px">
                                            <a class="icon-action" href="<?= url('mesas/form.php?id=' . (int)$mesa['id']) ?>" title="Editar">
                                                <span class="material-symbols-outlined">edit</span>
                                            </a>
                                            <a class="icon-action danger" data-confirm="Excluir esta mesa? Os consumos e reservas vinculados precisam ser removidos antes." href="<?= url('mesas/delete.php?id=' . (int)$mesa['id']) ?>" title="Excluir">
                                                <span class="material-symbols-outlined">delete</span>
                                            </a>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php if (!$mesas): ?>
                                <div class="empty-state">
                                    <span class="material-symbols-outlined">table_restaurant</span>
                                    <p>Cadastre a primeira mesa para começar.</p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </section>
                        <div style="height:20px">
                        </div>
                        <section class="card">
                            <div class="card-header">
                                <div>
                                    <h3>Lista detalhada</h3>
                                    <span>Informações administrativas</span>
                                </div>
                            </div>
                            <div class="data-table-wrap">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Mesa</th>
                                            <th>Capacidade</th>
                                            <th>Status</th>
                                            <th>Consumo de hoje</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($mesas as $mesa): ?>
                                        <tr>
                                            <td>
                                                <div class="cell-title">Mesa <?= e($mesa['numero']) ?>
                                                </div>
                                                <div class="cell-sub">
                                                    <?= e($mesa['observacoes'] ?: 'Sem observações') ?>
                                                </div>
                                            </td>
                                            <td>
                                                <?= (int)$mesa['capacidade'] ?> pessoas</td>
                                                <td>
                                                    <span class="status-badge <?= status_class($mesa['status_dia']) ?>">
                                                        <?= status_label($mesa['status_dia']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <strong>
                                                        <?= money($mesa['total_gasto']) ?>
                                                    </strong>
                                                </td>
                                                <td>
                                                    <div class="row-actions">
                                                        <a class="icon-action" href="<?= url('mesas/form.php?id=' . (int)$mesa['id']) ?>">
                                                            <span class="material-symbols-outlined">edit</span>
                                                        </a>
                                                        <a class="icon-action danger" data-confirm="Excluir esta mesa?" href="<?= url('mesas/delete.php?id=' . (int)$mesa['id']) ?>">
                                                            <span class="material-symbols-outlined">delete</span>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </section>
                            <?php require __DIR__ . '/../partials/footer.php'; ?>
