<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Método não permitido.');
}
verify_csrf();
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id || $id < 1) {
    flash('error', 'Reserva inválida.');
    redirect('reservas/index.php');
}

$stmt = $pdo->prepare('SELECT mesa_id FROM reservas WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
$mesaId = (int)$stmt->fetchColumn();
if (!$mesaId) {
    flash('error', 'A reserva não foi encontrada.');
    redirect('reservas/index.php');
}

$stmt = $pdo->prepare('DELETE FROM reservas WHERE id = ?');
$stmt->execute([$id]);
update_table_status_from_reservation($pdo, $mesaId);
flash('success', 'Reserva excluída com sucesso.');
redirect('reservas/index.php');
