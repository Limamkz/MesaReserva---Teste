<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';

require_admin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('clientes/index.php');
}
verify_csrf();

$id = (int)($_POST['cliente_id'] ?? 0);
$key = (string)($_POST['chave_admin'] ?? '');
if (!hash_equals('@MesaReserva_231009', $key)) {
    flash('error', 'A chave de acesso administrativo está incorreta.');
    redirect('clientes/index.php');
}

try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare('SELECT * FROM clientes WHERE id = ? AND senha IS NOT NULL LIMIT 1 FOR UPDATE');
    $stmt->execute([$id]);
    $client = $stmt->fetch();
    if (!$client) {
        throw new RuntimeException('Conta de cliente não encontrada.');
    }

    $existing = $pdo->prepare('SELECT id FROM administradores WHERE email = ? LIMIT 1');
    $existing->execute([$client['email']]);
    if ($existing->fetch()) {
        throw new RuntimeException('Já existe um administrador com este e-mail.');
    }

    $insert = $pdo->prepare('INSERT INTO administradores (nome, email, senha, avatar, ativo, email_verificado_em, token_verificacao, token_expira_em, token_redefinicao_senha, token_redefinicao_expira, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $insert->execute([
        $client['nome'], $client['email'], $client['senha'], $client['avatar'],
        $client['ativo'], $client['email_verificado_em'], $client['token_verificacao'],
        $client['token_expira_em'], $client['token_redefinicao_senha'],
        $client['token_redefinicao_expira'], $client['created_at']
    ]);

    // O registro antigo deixa de existir em clientes. Removemos também os dados
    // dependentes, pois a conta promovida passa a ser exclusivamente administrativa.
    foreach (['reservas' => 'cliente_id', 'perguntas' => 'cliente_id', 'avaliacoes' => 'cliente_id'] as $dependentTable => $foreignKey) {
        $delete = $pdo->prepare("DELETE FROM `{$dependentTable}` WHERE `{$foreignKey}` = ?");
        $delete->execute([$id]);
    }
    $deleteClient = $pdo->prepare('DELETE FROM clientes WHERE id = ?');
    $deleteClient->execute([$id]);

    $pdo->commit();
    flash('success', 'Conta transferida para administradores. O cadastro antigo foi removido de clientes.');
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    flash('error', $e instanceof RuntimeException ? $e->getMessage() : 'Não foi possível transferir a conta para administradores.');
}

redirect('admins/index.php');
