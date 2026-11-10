<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$id = (int)($_GET['id'] ?? 0);
if ($id > 0) {
    try {
        $pdo->beginTransaction();
        $check = $pdo->prepare('SELECT id FROM clientes WHERE id = ? LIMIT 1 FOR UPDATE');
        $check->execute([$id]);
        if (!$check->fetch()) {
            throw new RuntimeException('Cliente não encontrado.');
        }
        foreach (['reservas' => 'cliente_id', 'perguntas' => 'cliente_id', 'avaliacoes' => 'cliente_id'] as $dependentTable => $foreignKey) {
            $delete = $pdo->prepare("DELETE FROM `{$dependentTable}` WHERE `{$foreignKey}` = ?");
            $delete->execute([$id]);
        }
        $deleteClient = $pdo->prepare('DELETE FROM clientes WHERE id = ?');
        $deleteClient->execute([$id]);
        $pdo->commit();
        flash('success', 'Cliente e seus dados vinculados foram excluídos definitivamente.');
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        flash('error', $e instanceof RuntimeException ? $e->getMessage() : 'Não foi possível excluir o cliente.');
    }
}
redirect('clientes/index.php');
