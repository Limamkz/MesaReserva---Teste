<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';

require_login();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('perfil.php');
}
verify_csrf();

$id = current_user_id();
$table = is_admin() ? 'administradores' : 'clientes';
$stmt = $pdo->prepare("SELECT senha FROM {$table} WHERE id = ? LIMIT 1");
$stmt->execute([$id]);
$passwordHash = $stmt->fetchColumn();

if (!$passwordHash || !password_verify((string)($_POST['senha_atual'] ?? ''), (string)$passwordHash)) {
    flash('error', 'A senha informada está incorreta.');
    redirect('perfil.php');
}

if (is_admin()) {
    $activeAdmins = (int)$pdo->query('SELECT COUNT(*) FROM administradores WHERE ativo = 1')->fetchColumn();
    if ($activeAdmins <= 1) {
        flash('error', 'Não é possível excluir o último administrador ativo.');
        redirect('perfil.php');
    }
}

try {
    $pdo->beginTransaction();

    if (is_cliente()) {
        // Remove os registros dependentes para que a conta seja realmente apagada,
        // sem deixar uma conta anonimizada ou inativa na tabela clientes.
        foreach (['reservas' => 'cliente_id', 'perguntas' => 'cliente_id', 'avaliacoes' => 'cliente_id'] as $dependentTable => $foreignKey) {
            $delete = $pdo->prepare("DELETE FROM `{$dependentTable}` WHERE `{$foreignKey}` = ?");
            $delete->execute([$id]);
        }
    }

    $deleteAccount = $pdo->prepare("DELETE FROM `{$table}` WHERE id = ?");
    $deleteAccount->execute([$id]);
    if ($deleteAccount->rowCount() !== 1) {
        throw new RuntimeException('A conta não foi encontrada para exclusão.');
    }

    $pdo->commit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('MesaReserva account deletion: ' . $e->getMessage());
    flash('error', 'Não foi possível excluir a conta. Tente novamente.');
    redirect('perfil.php');
}

logout_user();
header('Location: ' . url('index.php'));
exit;
