<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$pageTitle='Consumos';
$consumos=$pdo->query(
"SELECT c.*,m.numero mesa_numero,(c.quantidade*c.valor_unitario) total
FROM consumos c INNER JOIN mesas m ON m.id=c.mesa_id
ORDER BY c.created_at DESC"
)->fetchAll();
$total=(float)$pdo->query("SELECT COALESCE(SUM(quantidade*valor_unitario),0) FROM consumos")->fetchColumn();
$resumoPeriodo = $pdo->query(
    "SELECT
      COALESCE(SUM(CASE WHEN DATE(c.created_at)=CURDATE() THEN c.quantidade*c.valor_unitario ELSE 0 END),0) AS diario,
      COALESCE(SUM(CASE WHEN YEARWEEK(c.created_at,1)=YEARWEEK(CURDATE(),1) THEN c.quantidade*c.valor_unitario ELSE 0 END),0) AS semanal,
      COALESCE(SUM(CASE WHEN YEAR(c.created_at)=YEAR(CURDATE()) AND MONTH(c.created_at)=MONTH(CURDATE()) THEN c.quantidade*c.valor_unitario ELSE 0 END),0) AS mensal,
      COALESCE(SUM(CASE WHEN YEAR(c.created_at)=YEAR(CURDATE()) THEN c.quantidade*c.valor_unitario ELSE 0 END),0) AS anual
     FROM consumos c"
)->fetch();
$porMesa = $pdo->query(
    "SELECT m.numero,
      COALESCE(SUM(CASE WHEN DATE(c.created_at)=CURDATE() THEN c.quantidade*c.valor_unitario ELSE 0 END),0) AS diario,
      COALESCE(SUM(CASE WHEN YEARWEEK(c.created_at,1)=YEARWEEK(CURDATE(),1) THEN c.quantidade*c.valor_unitario ELSE 0 END),0) AS semanal,
      COALESCE(SUM(CASE WHEN YEAR(c.created_at)=YEAR(CURDATE()) AND MONTH(c.created_at)=MONTH(CURDATE()) THEN c.quantidade*c.valor_unitario ELSE 0 END),0) AS mensal,
      COALESCE(SUM(CASE WHEN YEAR(c.created_at)=YEAR(CURDATE()) THEN c.quantidade*c.valor_unitario ELSE 0 END),0) AS anual
     FROM mesas m LEFT JOIN consumos c ON c.mesa_id=m.id
     GROUP BY m.id, m.numero ORDER BY m.numero"
)->fetchAll();

require __DIR__.'/../partials/header.php';
?>
<div class="page-heading">
    <div>
        <h2>Consumos</h2>
        <p>Registre itens e acompanhe o valor gasto em cada mesa.</p>
    </div>
    <a class="btn btn-primary" href="<?= url('consumos/form.php') ?>">
        <span class="material-symbols-outlined">add_shopping_cart</span>Novo consumo</a>
        </div>
        <section class="stats-grid" style="grid-template-columns:1fr 1fr">
            <div class="stat-card">
                <div class="stat-top">
                    <div class="stat-icon">
                        <span class="material-symbols-outlined">receipt_long</span>
                    </div>
                </div>
                <div class="stat-value">
                    <?= count($consumos) ?>
                </div>
                <div class="stat-label">Lançamentos registrados</div>
            </div>
            <div class="stat-card">
                <div class="stat-top">
                    <div class="stat-icon">
                        <span class="material-symbols-outlined">payments</span>
                    </div>
                </div>
                <div class="stat-value">
                    <?= money($total) ?>
                </div>
                <div class="stat-label">Valor total registrado</div>
            </div>
        </section>
        <section class="stats-grid" style="grid-template-columns:repeat(4,minmax(0,1fr));margin-bottom:20px">
            <div class="stat-card"><div class="stat-top"><div class="stat-icon"><span class="material-symbols-outlined">today</span></div></div><div class="stat-value"><?= money($resumoPeriodo['diario']) ?></div><div class="stat-label">Consumo diário</div></div>
            <div class="stat-card"><div class="stat-top"><div class="stat-icon"><span class="material-symbols-outlined">date_range</span></div></div><div class="stat-value"><?= money($resumoPeriodo['semanal']) ?></div><div class="stat-label">Consumo semanal</div></div>
            <div class="stat-card"><div class="stat-top"><div class="stat-icon"><span class="material-symbols-outlined">calendar_month</span></div></div><div class="stat-value"><?= money($resumoPeriodo['mensal']) ?></div><div class="stat-label">Consumo mensal</div></div>
            <div class="stat-card"><div class="stat-top"><div class="stat-icon"><span class="material-symbols-outlined">event</span></div></div><div class="stat-value"><?= money($resumoPeriodo['anual']) ?></div><div class="stat-label">Consumo anual</div></div>
        </section>
        <section class="card" style="margin-bottom:20px">
            <div class="card-header"><div><h3>Comparativo de consumo por mesa</h3><span>Valores separados por período</span></div></div>
            <div class="data-table-wrap"><table class="data-table">
                <thead><tr><th>Mesa</th><th>Hoje</th><th>Esta semana</th><th>Este mês</th><th>Este ano</th></tr></thead>
                <tbody><?php foreach ($porMesa as $pm): ?><tr>
                    <td><strong>Mesa <?= e($pm['numero']) ?></strong></td>
                    <td><?= money($pm['diario']) ?></td><td><?= money($pm['semanal']) ?></td><td><?= money($pm['mensal']) ?></td><td><?= money($pm['anual']) ?></td>
                </tr><?php endforeach; ?></tbody>
            </table></div>
        </section>
        <section class="card">
            <div class="card-header"><div><h3>Lançamentos de consumo</h3><span>Histórico completo; o total nas mesas é reiniciado diariamente por cálculo de data</span></div></div>
            <div class="data-table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Mesa</th>
                            <th>Descrição</th>
                            <th>Qtd.</th>
                            <th>Unitário</th>
                            <th>Total</th>
                            <th>Data</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($consumos as $c): ?>
                        <tr>
                            <td>
                                <span class="cell-title">Mesa <?= e($c['mesa_numero']) ?>
                                </span>
                            </td>
                            <td>
                                <?= e($c['descricao']) ?>
                            </td>
                            <td>
                                <?= e($c['quantidade']) ?>
                            </td>
                            <td>
                                <?= money($c['valor_unitario']) ?>
                            </td>
                            <td>
                                <strong>
                                    <?= money($c['total']) ?>
                                </strong>
                            </td>
                            <td>
                                <?= date('d/m/Y H:i',strtotime($c['created_at'])) ?>
                            </td>
                            <td>
                                <div class="row-actions">
                                    <a class="icon-action" href="<?= url('consumos/form.php?id='.$c['id']) ?>">
                                        <span class="material-symbols-outlined">edit</span>
                                    </a>
                                    <a class="icon-action danger" data-confirm="Excluir este consumo?" href="<?= url('consumos/delete.php?id='.$c['id']) ?>">
                                        <span class="material-symbols-outlined">delete</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php if(!$consumos): ?>
            <div class="empty-state">
                <span class="material-symbols-outlined">receipt_long</span>
                <p>Nenhum consumo registrado.</p>
            </div>
            <?php endif; ?>
        </section>
        <?php require __DIR__.'/../partials/footer.php'; ?>
