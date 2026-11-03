<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';

$message = null;
$success = false;
$messageType = 'error';
$email = $_SESSION['verification_email'] ?? '';

if (isset($_GET['created'])) {
if (($_SESSION['verification_sent'] ?? false) === true) {
$message = 'Conta criada. Enviamos o link de confirmação para o seu e-mail.';
$messageType = 'success';
} elseif (isset($_SESSION['verification_sent'])) {
$message = 'Sua conta foi criada, mas o e-mail não pôde ser enviado. Confira a configuração SMTP e tente reenviar.';
$messageType = 'error';
}
unset($_SESSION['verification_sent'], $_SESSION['verification_mail_error']);
}

$token = trim($_GET['token'] ?? '');
if ($token !== '') {
$hash = verification_hash($token);
$s = $pdo->prepare("SELECT id,nome,email,senha,tipo,cliente_id,avatar,ativo,email_verificado_em FROM usuarios WHERE token_verificacao=? AND token_expira_em>=NOW() LIMIT 1");
$s->execute([$hash]);
$u = $s->fetch();

if ($u) {
$pdo->prepare("UPDATE usuarios SET email_verificado_em=NOW(),token_verificacao=NULL,token_expira_em=NULL WHERE id=?")->execute([$u['id']]);
$u['email_verificado_em'] = date('Y-m-d H:i:s');
login_user($u);
unset($_SESSION['verification_email'], $_SESSION['verification_sent'], $_SESSION['verification_mail_error']);
redirect($u['tipo'] === 'admin' ? 'dashboard.php' : 'cliente.php');
} else {
$message = 'O link é inválido, já foi usado ou expirou.';
}
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resend'])) {
verify_csrf();
$email = $_SESSION['verification_email'] ?? '';

if ($email !== '') {
$s = $pdo->prepare("SELECT id,nome,email FROM usuarios WHERE email=? AND email_verificado_em IS NULL LIMIT 1");
$s->execute([$email]);
$u = $s->fetch();

if ($u) {
$newToken = verification_token();
$pdo->prepare("UPDATE usuarios SET token_verificacao=?,token_expira_em=DATE_ADD(NOW(),INTERVAL 24 HOUR) WHERE id=?")
->execute([verification_hash($newToken),$u['id']]);
try {
send_verification_email($u['nome'],$u['email'],$newToken);
$message = 'Um novo link de confirmação foi enviado para o seu e-mail.';
$messageType = 'success';
} catch(Throwable $e) {
$message = 'Não foi possível enviar. Confira SMTP_USERNAME e SMTP_PASSWORD em config/mail.php.';
$messageType = 'error';
}
} else {
$message = 'Esta conta já foi verificada ou não está mais pendente.';
}
} else {
$message = 'Não há um cadastro pendente nesta sessão. Crie sua conta ou faça login para continuar.';
}
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1.0">
        <title>Verificar e-mail | MesaReserva</title>
        <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,300..600,0..1,0..200" rel="stylesheet">
        <link rel="stylesheet" href="<?= url('assets/css/style.css?v=20261008painel3') ?>">
    </head>
    <body>
        <div class="center-page">
            <div class="verify-card">
                <a href="<?= url() ?>">
                    <img class="login-logo" src="<?= url('assets/img/logo.png') ?>" alt="MesaReserva">
                </a>
            <?php if($success): ?>
            <div class="verify-icon success">
                <span class="material-symbols-outlined">mark_email_read</span>
            </div>
            <h1>E-mail confirmado!</h1>
            <p>
                <?= e($message) ?>
            </p>
            <a class="btn btn-primary" href="<?= url('login.php') ?>">Ir para o login</a>
            <?php else: ?>
            <div class="verify-icon">
                <span class="material-symbols-outlined">mark_email_unread</span>
            </div>
            <h1>Confirme seu e-mail</h1>
            <p>Abra a mensagem que enviamos e clique no botão de confirmação. A verificação acontece automaticamente pelo link e ele é válido por 24 horas.</p>
            <?php if($message): ?>
            <div class="alert alert-<?= e($messageType) ?>">
                <?= e($message) ?>
            </div>
            <?php endif; ?>
            <?php if($email !== ''): ?>
            <div class="verification-address">
                <span class="material-symbols-outlined">mail</span>
                <div>
                    <small>Link enviado para</small>
                    <strong>
                        <?= e($email) ?>
                    </strong>
                </div>
            </div>
            <form method="post" class="verify-resend-form">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <button class="btn btn-light login-submit" type="submit" name="resend" value="1">Reenviar link de confirmação</button>
            </form>
            <?php endif; ?>
            <a class="back-link" href="<?= url('login.php') ?>">Voltar para o login</a>
            <?php endif; ?>
        </div>
    </div>
<?php require __DIR__.'/partials/footer.php'; ?>
</body>
</html>
