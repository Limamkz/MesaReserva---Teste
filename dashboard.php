<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

// Atualiza reservas cujo horário já passou antes de exibir os dados.
$pdo->exec("UPDATE reservas SET status='expirada' WHERE status IN ('pendente','confirmada') AND TIMESTAMP(data_reserva, hora_reserva) < NOW();");
require_admin();

$pageTitle = 'Dashboard';

$totalMesas = (int)$pdo->query("SELECT COUNT(*) FROM mesas")->fetchColumn();
$disponiveis = (int)$pdo->query("SELECT COUNT(*) FROM mesas m WHERE m.status <> 'ocupada' AND NOT EXISTS (SELECT 1 FROM reservas r WHERE r.mesa_id=m.id AND r.data_reserva=CURDATE() AND r.status='confirmada' AND TIMESTAMP(r.data_reserva,r.hora_reserva)>=NOW())")->fetchColumn();
$reservadas = (int)$pdo->query("SELECT COUNT(*) FROM mesas m WHERE m.status <> 'ocupada' AND EXISTS (SELECT 1 FROM reservas r WHERE r.mesa_id=m.id AND r.data_reserva=CURDATE() AND r.status='confirmada' AND TIMESTAMP(r.data_reserva,r.hora_reserva)>=NOW())")->fetchColumn();
$ocupadas = (int)$pdo->query("SELECT COUNT(*) FROM mesas WHERE status = 'ocupada'")->fetchColumn();

$consumoTotal = (float)$pdo->query("SELECT COALESCE(SUM(quantidade * valor_unitario),0) FROM consumos")->fetchColumn();

$mesas = $pdo->query(
"SELECT m.*,
CASE WHEN m.status='ocupada' THEN 'ocupada'
     WHEN EXISTS (SELECT 1 FROM reservas r WHERE r.mesa_id=m.id AND r.data_reserva=CURDATE() AND r.status='confirmada' AND TIMESTAMP(r.data_reserva,r.hora_reserva)>=NOW()) THEN 'reservada'
     ELSE 'disponivel' END AS status_dia,
COALESCE((SELECT SUM(c.quantidade*c.valor_unitario) FROM consumos c WHERE c.mesa_id=m.id AND DATE(c.created_at)=CURDATE()),0) AS total_gasto
FROM mesas m
ORDER BY m.numero"
)->fetchAll();

$reservasHoje = $pdo->query(
"SELECT r.*, c.nome AS cliente_nome, m.numero AS mesa_numero
FROM reservas r
INNER JOIN clientes c ON c.id = r.cliente_id
INNER JOIN mesas m ON m.id = r.mesa_id
WHERE r.data_reserva = CURDATE()
AND r.status IN ('pendente','confirmada')
ORDER BY r.hora_reserva ASC
LIMIT 7"
 )->fetchAll();

$periodo = $_GET['periodo'] ?? 'mensal';
$periodo = in_array($periodo, ['diario','semanal','mensal','anual'], true) ? $periodo : 'mensal';
$range = match ($periodo) {
    'diario' => "DATE(c.created_at) >= CURDATE() - INTERVAL 6 DAY",
    'semanal' => "YEARWEEK(c.created_at, 1) >= YEARWEEK(CURDATE() - INTERVAL 7 WEEK, 1)",
    'anual' => "YEAR(c.created_at) >= YEAR(CURDATE()) - 4",
    default => "c.created_at >= DATE_SUB(DATE_FORMAT(CURDATE(), '%Y-%m-01'), INTERVAL 11 MONTH)"
};
// Compara o valor total gasto em cada mesa dentro do período selecionado.
$chartStmt = $pdo->query("SELECT CONCAT('Mesa ', m.numero) AS rotulo,
        COALESCE(SUM(c.quantidade * c.valor_unitario), 0) AS total
    FROM mesas m
    LEFT JOIN consumos c ON c.mesa_id = m.id AND {$range}
    GROUP BY m.id, m.numero
    ORDER BY m.numero");
$chartRows = $chartStmt->fetchAll();
$chartLabels = array_column($chartRows, 'rotulo');
$chartValues = array_map('floatval', array_column($chartRows, 'total'));

if (isset($_GET['ajax']) && $_GET['ajax'] === 'grafico') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['labels' => $chartLabels, 'values' => $chartValues], JSON_UNESCAPED_UNICODE);
    exit;
}

require __DIR__ . '/partials/header.php';
?>
<div class="page-heading">
    <div>
        <h2>Visão geral do salão</h2>
        <p>Acompanhe o estado das mesas e a operação do restaurante em tempo real.</p>
    </div>
    <div class="actions">
        <a class="btn btn-light" href="<?= url('consumos/form.php') ?>">
            <span class="material-symbols-outlined">add_shopping_cart</span>Novo consumo</a>
                <a class="btn btn-primary" href="<?= url('reservas/form.php') ?>">
                    <span class="material-symbols-outlined">add</span>Nova reserva</a>
                    </div>
                </div>
                <section class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-top">
                            <div class="stat-icon">
                                <span class="material-symbols-outlined">table_restaurant</span>
                            </div>
                        </div>
                        <div class="stat-value">
                            <?= $totalMesas ?>
                        </div>
                        <div class="stat-label">Total de mesas</div>
                    </div>
                    <div class="stat-card stat-accent-green">
                        <div class="stat-top">
                            <div class="stat-icon">
                                <span class="material-symbols-outlined">check_circle</span>
                            </div>
                        </div>
                        <div class="stat-value">
                            <?= $disponiveis ?>
                        </div>
                        <div class="stat-label">Mesas disponíveis</div>
                    </div>
                    <div class="stat-card stat-accent-yellow">
                        <div class="stat-top">
                            <div class="stat-icon">
                                <span class="material-symbols-outlined">event</span>
                            </div>
                        </div>
                        <div class="stat-value">
                            <?= $reservadas ?>
                        </div>
                        <div class="stat-label">Mesas reservadas</div>
                    </div>
                    <div class="stat-card stat-accent-red">
                        <div class="stat-top">
                            <div class="stat-icon">
                                <span class="material-symbols-outlined">restaurant</span>
                            </div>
                        </div>
                        <div class="stat-value">
                            <?= $ocupadas ?>
                        </div>
                        <div class="stat-label">Mesas ocupadas</div>
                    </div>
                </section>
                <div class="grid-2">
                    <section class="card">
                        <div class="card-header">
                            <div>
                                <h3>Mapa das mesas</h3>
                                <span>Capacidade e consumo de hoje</span>
                            </div>
                            <a href="<?= url('mesas/index.php') ?>" class="btn btn-light btn-sm">Gerenciar</a>
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
                                        <?php if (!$mesas): ?>
                                        <div class="empty-state">
                                            <span class="material-symbols-outlined">table_restaurant</span>
                                            <p>Nenhuma mesa cadastrada.</p>
                                        </div>
                                        <?php else: ?>
                                        <div class="table-map">
                                            <?php foreach ($mesas as $mesa): ?>
                                            <a class="table-card <?= e($mesa['status_dia']) ?>" href="<?= url('mesas/form.php?id=' . (int)$mesa['id']) ?>">
                                                <div class="table-top">
                                                    <div>
                                                        <div class="table-number">Mesa <?= e($mesa['numero']) ?>
                                                        </div>
                                                        <div class="capacity">
                                                            <?= (int)$mesa['capacidade'] ?> pessoas</div>
                                                        </div>
                                                        <span class="status-badge <?= status_class($mesa['status_dia']) ?>">
                                                            <?= status_label($mesa['status_dia']) ?>
                                                        </span>
                                                    </div>
                                                    <div class="table-total">Consumo de hoje<strong>
                                                        <?= money($mesa['total_gasto']) ?>
                                                    </strong>
                                                </div>
                                            </a>
                                            <?php endforeach; ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </section>
                                <section class="card">
                                    <div class="card-header">
                                        <div>
                                            <h3>Reservas de hoje</h3>
                                            <span>
                                                <?= date('d/m/Y') ?>
                                            </span>
                                        </div>
                                        <a href="<?= url('reservas/index.php') ?>" class="btn btn-light btn-sm">Ver todas</a>
                                    </div>
                                    <div class="card-body">
                                        <?php if (!$reservasHoje): ?>
                                        <div class="empty-state">
                                            <span class="material-symbols-outlined">event_available</span>
                                            <p>Nenhuma reserva para hoje.</p>
                                        </div>
                                        <?php else: ?>
                                        <div class="reservation-list">
                                            <?php foreach ($reservasHoje as $r): ?>
                                            <div class="reservation-row">
                                                <div class="reservation-main">
                                                    <div class="reservation-time">
                                                        <?= date('H:i', strtotime($r['hora_reserva'])) ?>
                                                    </div>
                                                    <div>
                                                        <strong>
                                                            <?= e($r['cliente_nome']) ?>
                                                        </strong>
                                                        <small>Mesa <?= e($r['mesa_numero']) ?> · <?= (int)$r['pessoas'] ?> pessoas</small>
                                                    </div>
                                                </div>
                                                <span class="status-badge <?= status_class($r['status']) ?>">
                                                    <?= status_label($r['status']) ?>
                                                </span>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </section>
                            </div>
                            <section class="card dashboard-chart-card" style="margin-bottom:22px">
    <div class="card-header"><div><h3>Desempenho de consumo</h3><span>Valor total gasto em cada mesa no período selecionado</span></div>
        <form id="chartPeriodForm" method="get"><select id="chartPeriod" class="form-control" name="periodo" aria-label="Período do gráfico">
            <option value="diario" <?= $periodo==='diario'?'selected':'' ?>>Diário · últimos 7 dias</option>
            <option value="semanal" <?= $periodo==='semanal'?'selected':'' ?>>Semanal · últimas 8 semanas</option>
            <option value="mensal" <?= $periodo==='mensal'?'selected':'' ?>>Mensal · últimos 12 meses</option>
            <option value="anual" <?= $periodo==='anual'?'selected':'' ?>>Anual · últimos 5 anos</option>
        </select></form>
    </div>
    <div class="card-body"><div style="height:300px;position:relative"><canvas id="consumptionChart" aria-label="Gráfico de valor gasto por mesa"></canvas></div></div>
</section>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const canvas = document.getElementById('consumptionChart');
    const selector = document.getElementById('chartPeriod');
    if (!canvas || typeof Chart === 'undefined') return;
    const chart = new Chart(canvas, {
        type: 'bar',
        data: { labels: <?= json_encode($chartLabels, JSON_UNESCAPED_UNICODE) ?>,
            datasets: [{ label: 'Valor gasto (R$)', data: <?= json_encode($chartValues) ?>,
                borderRadius: 9, maxBarThickness: 42, backgroundColor: 'rgba(190, 126, 63, .76)', borderColor: '#be7e3f', borderWidth: 1 }] },
        options: { responsive: true, maintainAspectRatio: false, animation: { duration: 220 },
            interaction: { intersect: false, mode: 'index' },
            plugins: { legend: { display: false }, tooltip: { callbacks: { label: c => 'R$ ' + c.parsed.y.toLocaleString('pt-BR',{minimumFractionDigits:2}) } } },
            scales: { x: { title: { display: true, text: 'Mesa' }, grid: { display: false }, ticks: { autoSkip: false, maxRotation: 0 } }, y: { title: { display: true, text: 'Valor gasto (R$)' }, beginAtZero: true, grid: { color: 'rgba(80,65,45,.08)' }, ticks: { callback: v => 'R$ ' + v.toLocaleString('pt-BR') } } } }
    });
    selector?.addEventListener('change', async () => {
        const previous = selector.value;
        selector.disabled = true;
        try {
            const url = new URL(window.location.href);
            url.searchParams.set('periodo', previous);
            url.searchParams.set('ajax', 'grafico');
            const response = await fetch(url.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if (!response.ok) throw new Error('Falha ao carregar o gráfico');
            const result = await response.json();
            chart.data.labels = result.labels;
            chart.data.datasets[0].data = result.values;
            chart.update('none');
        } catch (error) {
            console.error(error);
            selector.value = <?= json_encode($periodo) ?>;
        } finally { selector.disabled = false; }
    });
});
</script>
<div style="height:20px">
                            </div>
                            <section class="card">
                                <div class="card-header">
                                    <div>
                                        <h3>Atalhos</h3>
                                        <span>Operações frequentes</span>
                                    </div>
                                    <div class="kpi-inline">
                                        <strong>
                                            <?= money($consumoTotal) ?>
                                        </strong>
                                        <span>consumo registrado</span>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="quick-actions">
                                        <a class="quick-action" href="<?= url('mesas/form.php') ?>">
                                            <span class="material-symbols-outlined">add_business</span>
                                            <div>
                                                <strong>Cadastrar mesa</strong>
                                                <small>Defina capacidade e status inicial.</small>
                                            </div>
                                        </a>
                                        <a class="quick-action" href="<?= url('clientes/form.php') ?>">
                                            <span class="material-symbols-outlined">person_add</span>
                                            <div>
                                                <strong>Cadastrar cliente</strong>
                                                <small>Organize os dados dos clientes.</small>
                                            </div>
                                        </a>
                                        <a class="quick-action" href="<?= url('reservas/form.php') ?>">
                                            <span class="material-symbols-outlined">calendar_add_on</span>
                                            <div>
                                                <strong>Registrar reserva</strong>
                                                <small>Escolha cliente, mesa e horário.</small>
                                            </div>
                                        </a>
                                        <a class="quick-action" href="<?= url('consumos/form.php') ?>">
                                            <span class="material-symbols-outlined">receipt_long</span>
                                            <div>
                                                <strong>Lançar consumo</strong>
                                                <small>Atualize o gasto de cada mesa.</small>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            </section>
                            <?php require __DIR__ . '/partials/footer.php'; ?>
