<?php declare(strict_types=1); require_once __DIR__.'/../config/admin_guard.php';$pageTitle='Avaliações';$stats=$pdo->query("SELECT COUNT(*) total,COALESCE(AVG(nota),0) media FROM avaliacoes")->fetch();$items=$pdo->query("SELECT a.*,u.nome FROM avaliacoes a INNER JOIN usuarios u ON u.id=a.usuario_id ORDER BY a.created_at DESC")->fetchAll();require __DIR__.'/../partials/header.php'; ?>
<div class="page-heading">
    <div>
        <h2>Avaliações dos clientes</h2>
        <p>Acompanhe a percepção dos clientes sobre o serviço.</p>
    </div>
</div>
<section class="stats-grid" style="grid-template-columns:1fr 1fr">
    <div class="stat-card">
        <div class="stat-value">
            <?= (int)$stats['total'] ?>
        </div>
        <div class="stat-label">Avaliações recebidas</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">
            <?= number_format((float)$stats['media'],1,',','.') ?>/5</div>
            <div class="stat-label">Nota média</div>
        </div>
    </section>
    <section class="card">
        <div class="data-table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Nota</th>
                        <th>Comentário</th>
                        <th>Data</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($items as $a): ?>
                    <tr>
                        <td class="cell-title">
                            <?= e($a['nome']) ?>
                        </td>
                        <td>
                            <span class="stars">
                                <?= str_repeat('★',(int)$a['nota']) ?>
                            </span>
                        </td>
                        <td>
                            <?= e($a['comentario']?:'Sem comentário') ?>
                        </td>
                        <td>
                            <?= date('d/m/Y H:i',strtotime($a['created_at'])) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
    <?php require __DIR__.'/../partials/footer.php'; ?>
