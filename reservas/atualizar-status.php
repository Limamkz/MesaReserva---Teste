<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('reservas/index.php');
}
verify_csrf();

$id = (int)($_POST['id'] ?? 0);
$status = (string)($_POST['status'] ?? '');

$allowed = ['pendente', 'confirmada', 'cancelada', 'concluida'];
if ($id < 1 || !in_array($status, $allowed, true)) {
    flash('error', 'Status de reserva inválido.');
    redirect('reservas/index.php');
}

try {
    $stmt = $pdo->prepare('SELECT id, mesa_id, data_reserva, hora_reserva, status FROM reservas WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $reserva = $stmt->fetch();

    if (!$reserva) {
        flash('error', 'Reserva não encontrada.');
        redirect('reservas/index.php');
    }

    if (in_array($reserva['status'], ['expirada', 'cancelada', 'concluida'], true)) {
        flash('error', 'Esta reserva já foi encerrada e não pode ser alterada por aqui.');
        redirect('reservas/index.php');
    }

    if (strtotime($reserva['data_reserva'] . ' ' . $reserva['hora_reserva']) < time()) {
        $pdo->prepare("UPDATE reservas SET status='expirada' WHERE id=?")->execute([$id]);
        update_table_status_from_reservation($pdo, (int)$reserva['mesa_id']);
        flash('error', 'O horário da reserva passou. Ela foi marcada como expirada.');
        redirect('reservas/index.php');
    }

    $pdo->prepare('UPDATE reservas SET status = ? WHERE id = ?')->execute([$status, $id]);

    if ($status === 'confirmada') {
        $pdo->prepare("UPDATE mesas SET status='reservada' WHERE id=? AND status<>'ocupada'")
            ->execute([(int)$reserva['mesa_id']]);
        flash('success', 'Reserva confirmada.');
    } elseif ($status === 'cancelada' || $status === 'concluida') {
        update_table_status_from_reservation($pdo, (int)$reserva['mesa_id']);
        flash('success', $status === 'cancelada' ? 'Reserva recusada.' : 'Reserva marcada como concluída.');
    } else {
        flash('success', 'Reserva voltou para pendente.');
    }
} catch (PDOException $e) {
    flash('error', 'Não foi possível atualizar o status da reserva.');
}

redirect('reservas/index.php');
