<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';
require_admin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('clientes/index.php');
verify_csrf();
$usuarioId = (int)($_POST['usuario_id'] ?? 0);
$ativo = (int)($_POST['ativo'] ?? -1);
if ($usuarioId < 1 || !in_array($ativo, [0, 1], true)) {
    flash('error', 'Estado da conta inválido.');
    redirect('clientes/index.php');
}
$stmt = $pdo->prepare("UPDATE usuarios SET ativo = ? WHERE id = ? AND tipo = 'cliente'");
$stmt->execute([$ativo, $usuarioId]);
flash('success', $stmt->rowCount() ? ($ativo ? 'Conta ativada.' : 'Conta inativada.') : 'Nenhuma alteração necessária.');
redirect('clientes/index.php');
