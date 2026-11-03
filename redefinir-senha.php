<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';
$token = (string)($_GET['token'] ?? $_POST['token'] ?? '');
$hash = hash('sha256', $token);
$stmt = $pdo->prepare("SELECT id FROM usuarios WHERE token_redefinicao_senha=? AND token_redefinicao_expira>NOW() LIMIT 1");
$stmt->execute([$hash]); $user = $stmt->fetch();
$error = null; $done = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $senha = (string)($_POST['senha'] ?? ''); $confirmacao = (string)($_POST['confirmacao'] ?? '');
    if (!$user) $error = 'Este link é inválido ou expirou. Solicite uma nova verificação.';
    elseif (strlen($senha) < 8) $error = 'A senha deve ter pelo menos 8 caracteres.';
    elseif ($senha !== $confirmacao) $error = 'As senhas não coincidem.';
    else {
        $pdo->prepare("UPDATE usuarios SET senha=?, token_redefinicao_senha=NULL, token_redefinicao_expira=NULL WHERE id=?")->execute([password_hash($senha,PASSWORD_DEFAULT),$user['id']]);
        $done = true;
    }
}
?>
<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Nova senha | MesaReserva</title><link rel="stylesheet" href="<?= url('assets/css/style.css') ?>"></head><body class="auth-page"><header class="public-nav"><a href="<?= url('index.php') ?>" class="public-brand"><img src="<?= url('assets/img/logo.png') ?>" alt="MesaReserva"></a><nav><a href="<?= url('index.php') ?>">Início</a><a href="<?= url('privacidade.php') ?>">Privacidade</a></nav><div class="public-actions"><a class="btn btn-light btn-sm" href="<?= url('login.php') ?>">Entrar</a></div></header><main class="login-panel"><section class="login-box reset-password-box">
<a class="reset-password-brand" href="<?= url('index.php') ?>"><img src="<?= url('assets/img/logo.png') ?>" alt="MesaReserva"></a>
<div class="login-welcome"><h1>Digite a sua nova senha</h1><p>Escolha uma nova senha segura para continuar usando o MesaReserva.</p></div>
<?php if($error): ?><div class="login-error"><?= e($error) ?></div><?php endif; ?>
<?php if($done): ?><div class="alert alert-success">Senha alterada com sucesso. Você já pode entrar.</div><a class="btn btn-primary" href="<?= url('login.php') ?>">Ir para o login</a>
<?php elseif(!$user): ?><p>Link inválido ou expirado. Solicite uma nova redefinição.</p><a href="<?= url('esqueci-senha.php') ?>">Solicitar novo link</a>
<?php else: ?><form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="token" value="<?= e($token) ?>"><div class="form-group"><label for="nova-senha">Digite a sua nova senha</label><input id="nova-senha" class="form-control" type="password" name="senha" minlength="8" autocomplete="new-password" required></div><div class="form-group"><label for="confirme-senha">Confirme sua senha</label><input id="confirme-senha" class="form-control" type="password" name="confirmacao" minlength="8" autocomplete="new-password" required></div><button class="btn btn-primary" type="submit">Salvar nova senha</button></form><?php endif; ?></section></main><?php require __DIR__ . '/partials/footer-public.php'; ?>
</body></html>
