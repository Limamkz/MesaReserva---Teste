<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';

$message = null;
$messageType = 'error';
$email = (string)($_SESSION['verification_email'] ?? '');

if (isset($_GET['created'])) {
    $sent = !empty($_SESSION['verification_sent']);
    $message = $sent
        ? 'Enviamos um link de confirmação para seu e-mail. Seus dados ainda não foram gravados no banco.'
        : 'Não foi possível enviar o e-mail. Confira a configuração SMTP e tente reenviar.';
    $messageType = $sent ? 'success' : 'error';
    unset($_SESSION['verification_sent'], $_SESSION['verification_mail_error']);
}

$token = trim((string)($_GET['token'] ?? ''));
if ($token !== '') {
    $pending = get_pending_registration($token);
    if ($pending) {
        $table = ($pending['tipo'] ?? '') === 'admin' ? 'administradores' : 'clientes';
        try {
            $check = $pdo->prepare('SELECT id FROM clientes WHERE email = ? UNION SELECT id FROM administradores WHERE email = ? LIMIT 1');
            $check->execute([$pending['email'], $pending['email']]);
            if ($check->fetch()) {
                delete_pending_registration($token);
                $message = 'Este e-mail já possui uma conta cadastrada. Faça login.';
            } else {
                if ($table === 'administradores') {
                    $insert = $pdo->prepare('INSERT INTO administradores (nome, email, senha, ativo, email_verificado_em) VALUES (?, ?, ?, 1, NOW())');
                } else {
                    $insert = $pdo->prepare('INSERT INTO clientes (nome, email, senha, ativo, email_verificado_em) VALUES (?, ?, ?, 1, NOW())');
                }
                $insert->execute([$pending['nome'], $pending['email'], $pending['senha']]);
                delete_pending_registration($token);
                unset($_SESSION['verification_email'], $_SESSION['verification_type'], $_SESSION['verification_token']);
                flash('success', 'E-mail confirmado! Sua conta foi criada. Agora você já pode entrar.');
                redirect('login.php');
            }
        } catch (Throwable $e) {
            $message = 'Não foi possível concluir a verificação. Tente novamente ou solicite outro link.';
        }
    } else {
        $message = 'O link é inválido, já foi usado ou expirou. Solicite um novo link.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resend'])) {
    verify_csrf();
    $oldToken = (string)($_SESSION['verification_token'] ?? '');
    $pending = $oldToken !== '' ? get_pending_registration($oldToken) : null;
    if ($pending) {
        $newToken = verification_token();
        $pending['expires_at'] = time() + 86400;
        delete_pending_registration($oldToken);
        save_pending_registration($newToken, $pending);
        $_SESSION['verification_token'] = $newToken;
        try {
            send_verification_email($pending['nome'], $pending['email'], $newToken);
            $message = 'Um novo link de confirmação foi enviado.';
            $messageType = 'success';
        } catch (Throwable $e) {
            $message = 'Não foi possível enviar. Confira a configuração SMTP.';
        }
    } else {
        $message = 'O cadastro temporário não foi encontrado ou expirou. Faça o cadastro novamente.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Verificar e-mail | MesaReserva</title>
    <link rel="stylesheet" href="<?= url('assets/css/style.css?v=20261110verify2') ?>">
</head>
<body>
    <div class="center-page">
        <div class="verify-card">
            <a href="<?= url() ?>"><img class="login-logo" src="<?= url('assets/img/logo.png') ?>" alt="MesaReserva"></a>
            <div class="verify-icon"><span class="material-symbols-outlined">mark_email_unread</span></div>
            <h1>Confirme seu e-mail</h1>
            <p>Abra a mensagem que enviamos e clique no link de confirmação. Ele é válido por 24 horas.</p>
            <?php if ($message): ?><div class="alert alert-<?= e($messageType) ?>"><?= e($message) ?></div><?php endif; ?>
            <?php if ($email !== ''): ?>
                <div class="verification-address"><strong><?= e($email) ?></strong></div>
                <form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><button class="btn btn-light" name="resend" value="1">Reenviar link</button></form>
            <?php endif; ?>
            <a class="back-link" href="<?= url('login.php') ?>">Voltar para o login</a>
        </div>
    </div>
    <?php require __DIR__ . '/partials/footer-public.php'; ?>
</body>
</html>
