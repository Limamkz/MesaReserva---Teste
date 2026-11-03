<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$id = (int)($_GET['id'] ?? 0);
if ($id > 0) {
    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE cliente_id = ? AND tipo = 'cliente'");
        $stmt->execute([$id]);
        $userIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $stmt = $pdo->prepare("DELETE FROM clientes WHERE id = ?");
        $stmt->execute([$id]);
        if ($stmt->rowCount() < 1) {
            throw new RuntimeException('Cliente não encontrado.');
        }
        if ($userIds) {
            $deleteUser = $pdo->prepare("DELETE FROM usuarios WHERE id = ? AND tipo = 'cliente'");
            foreach ($userIds as $userId) $deleteUser->execute([(int)$userId]);
        }
        $pdo->commit();
        flash('success', 'Cliente e conta vinculada excluídos.');
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        flash('error', 'Não é possível excluir este cliente enquanto houver reservas vinculadas.');
    }
}
redirect('clientes/index.php');
