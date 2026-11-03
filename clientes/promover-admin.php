<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';
require_admin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('clientes/index.php');
verify_csrf();
$id = (int)($_POST['cliente_id'] ?? 0);
$key = (string)($_POST['chave_admin'] ?? '');
if (!hash_equals('@MesaReserva_231009', $key)) {
    flash('error', 'A chave de acesso administrativo está incorreta.');
    redirect('clientes/index.php');
}
$stmt = $pdo->prepare("SELECT u.id FROM usuarios u WHERE u.id=? AND u.tipo='cliente' LIMIT 1");
$stmt->execute([$id]);
if (!$stmt->fetch()) {
    flash('error', 'Cliente não encontrado ou já possui outro perfil.');
    redirect('clientes/index.php');
}
$pdo->prepare("UPDATE usuarios SET tipo='admin', cliente_id=NULL WHERE id=?")->execute([$id]);
flash('success', 'O cliente agora possui perfil de administrador. O acesso de cliente foi convertido.');
redirect('admins/index.php');
