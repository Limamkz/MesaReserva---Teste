<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';
$error = null; $success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = trim((string)($_POST['email'] ?? ''));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Informe um e-mail válido.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id,nome,email FROM usuarios WHERE email=? AND ativo=1 LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user) {
                $token = bin2hex(random_bytes(32));
                $hash = hash('sha256', $token);
                $pdo->prepare("UPDATE usuarios SET token_redefinicao_senha=?, token_redefinicao_expira=DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE id=?")->execute([$hash, $user['id']]);
                $link = rtrim(verification_base_url(), '/') . '/redefinir-senha.php?token=' . urlencode($token);
                $mensagem = "Olá, {$user['nome']}!\n\nPara criar uma nova senha, acesse o link abaixo (válido por 1 hora):\n\n{$link}\n\nSe não solicitou, ignore este e-mail.\n\nMesaReserva";
                send_smtp_mail($user['email'], 'Redefinição de senha — MesaReserva', $mensagem);
            }
            $success = true;
        } catch (Throwable $e) {
            error_log('MesaReserva password reset: ' . $e->getMessage());
            $error = 'Não foi possível concluir o envio. Confira as configurações SMTP e tente novamente.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Recuperar senha | MesaReserva</title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Material+Symbols+Outlined&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet"><link rel="stylesheet" href="<?= url('assets/css/style.css?v=20261103auth2') ?>"></head>
<body class="auth-page forgot-auth-page"><div class="login-page"><section class="login-visual"><div class="login-copy"><span class="eyebrow gold">SEGURANÇA MESARESERVA</span><h2>Recupere seu<br><span>acesso com segurança.</span></h2><p>Vamos enviar um link exclusivo para o e-mail cadastrado. Por segurança, ele expira em uma hora.</p></div></section>
<section class="login-panel"><div class="login-box forgot-box"><div class="auth-back-row"><a class="auth-back-button" href="<?= url('login.php') ?>"><span class="material-symbols-outlined">arrow_back</span>Voltar ao login</a><a class="auth-home-button" href="<?= url() ?>"><span class="material-symbols-outlined">home</span>Início</a></div>
<a class="forgot-logo-link" href="<?= url() ?>"><img class="login-logo" src="<?= url('assets/img/logo.png') ?>" alt="MesaReserva"></a><div class="login-welcome"><h1>Esqueceu sua senha?</h1><p>Informe o e-mail da sua conta para receber o link de redefinição.</p></div>
<?php if($error): ?><div class="login-error"><?= e($error) ?></div><?php endif; ?>
<?php if($success): ?><div class="alert alert-success">Se o e-mail estiver cadastrado, enviaremos as instruções para redefinir a senha.</div><a class="btn btn-primary login-submit forgot-back-link" href="<?= url('login.php') ?>">Voltar para entrar</a><?php else: ?>
<form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><div class="form-group"><label for="email-recuperacao">E-mail cadastrado</label><div class="input-icon"><span class="material-symbols-outlined">mail</span><input id="email-recuperacao" class="form-control" type="email" name="email" placeholder="seu@email.com" autocomplete="email" required></div></div><button class="btn btn-primary login-submit" type="submit">Enviar verificação <span class="material-symbols-outlined">arrow_forward</span></button></form><?php endif; ?>
<div class="login-links"><a href="<?= url('login.php') ?>">Lembrou sua senha? Entrar</a></div></div></section></div><?php require __DIR__ . '/partials/footer-public.php'; ?></body></html>