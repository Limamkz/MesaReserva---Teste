<?php declare(strict_types=1); require_once __DIR__.'/includes/functions.php';
$pdo->exec("UPDATE reservas SET status='expirada' WHERE status IN ('pendente','confirmada') AND TIMESTAMP(data_reserva, hora_reserva) < NOW();"); require_login(); if(is_admin()) redirect('dashboard.php'); $available=available_tables_count($pdo); $cid=current_client_id(); $reservas=[]; if($cid){$s=$pdo->prepare("SELECT r.*,m.numero mesa_numero FROM reservas r INNER JOIN mesas m ON m.id=r.mesa_id WHERE r.cliente_id=? ORDER BY r.data_reserva DESC,r.hora_reserva DESC LIMIT 5");$s->execute([$cid]);$reservas=$s->fetchAll();} require __DIR__.'/partials/client-header.php'; ?>
<div class="client-hero">
    <div>
        <span class="eyebrow gold">RESTAURANTE MESARESERVA</span>
        <h1>Olá, <?= e(current_user_name()) ?>.</h1>
        <p>Escolha seu melhor momento. A gente cuida da mesa.</p>
        <a class="btn btn-primary" href="<?= url('reservar.php') ?>">
            <span class="material-symbols-outlined">calendar_add_on</span>Reservar uma mesa</a>
            </div>
            <div class="availability-card">
                <span class="material-symbols-outlined">table_restaurant</span>
                <strong>
                    <?= $available ?>
                </strong>
                <span>mesas disponíveis agora</span>
            </div>
        </div>
        <div class="client-grid">
            <section class="card">
                <div class="card-header">
                    <div>
                        <h3>Minhas reservas</h3>
                        <span>Seus próximos momentos no restaurante</span>
                    </div>
                    <a class="btn btn-light btn-sm" href="<?= url('minhas-reservas.php') ?>">Ver todas</a>
                </div>
                <div class="card-body">
                    <?php if(!$reservas): ?>
                    <div class="empty-state">
                        <span class="material-symbols-outlined">event_available</span>
                        <p>Você ainda não possui reservas.</p>
                    </div>
                    <?php else: ?>
                    <div class="reservation-list">
                        <?php foreach($reservas as $r): ?>
                        <div class="reservation-row">
                            <div class="reservation-main">
                                <div class="reservation-time">
                                    <?= date('d/m',strtotime($r['data_reserva'])) ?>
                                    <small>
                                        <?= date('H:i',strtotime($r['hora_reserva'])) ?>
                                    </small>
                                </div>
                                <div>
                                    <strong>Mesa <?= e($r['mesa_numero']) ?>
                                    </strong>
                                    <small>
                                        <?= (int)$r['pessoas'] ?> pessoas · <?= status_label($r['status']) ?>
                                    </small>
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
            <section class="client-side-actions">
                <a class="client-action" href="<?= url('perguntas.php') ?>">
                    <span class="material-symbols-outlined">support_agent</span>
                    <div>
                        <strong>Precisa de ajuda?</strong>
                        <small>Fale com a administração sobre dúvidas ou problemas.</small>
                    </div>
                </a>
                <a class="client-action" href="<?= url('avaliacoes.php') ?>">
                    <span class="material-symbols-outlined">star</span>
                    <div>
                        <strong>Avalie sua experiência</strong>
                        <small>Conte como foi sua experiência no MesaReserva.</small>
                    </div>
                </a>
                <a class="client-action" href="<?= url('privacidade.php') ?>">
                    <span class="material-symbols-outlined">privacy_tip</span>
                    <div>
                        <strong>Privacidade</strong>
                        <small>Consulte como seus dados são tratados.</small>
                    </div>
                </a>
            </section>
        </div>
        <?php require __DIR__.'/partials/footer.php'; ?>
