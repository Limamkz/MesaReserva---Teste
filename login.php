<?php
declare(strict_types=1); require_once __DIR__.'/includes/functions.php';
if(is_logged_in()){ redirect(is_admin()?'dashboard.php':'cliente.php'); }
$error=null; $tipo=$_POST['tipo']??'cliente';
if($_SERVER['REQUEST_METHOD']==='POST'){verify_csrf();$email=trim($_POST['email']??'');$senha=$_POST['senha']??'';$tipo=$_POST['tipo']??'cliente';if(!in_array($tipo,['admin','cliente'],true)||!filter_var($email,FILTER_VALIDATE_EMAIL)||!$senha){$error='Preencha todos os campos corretamente.';}else{$table=$tipo==='admin'?'administradores':'clientes';$s=$pdo->prepare("SELECT id,nome,email,senha,avatar,ativo,email_verificado_em FROM {$table} WHERE email=? LIMIT 1");$s->execute([$email]);$u=$s->fetch();if($u&&(int)$u['ativo']===1&&!empty($u['senha'])&&password_verify($senha,$u['senha'])){if(empty($u['email_verificado_em'])){$error='Seu e-mail ainda não foi verificado. Confira sua caixa de entrada.';}else{$u['tipo']=$tipo;login_user($u);redirect($tipo==='admin'?'dashboard.php':'cliente.php');}}else{$error='E-mail, senha ou tipo de acesso inválidos.';}}}
?>
<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1.0">
        <title>Entrar | MesaReserva</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,300..600,0..1,0..200&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="<?= url('assets/css/style.css?v=20261110login1') ?>">
    </head>
    <body class="auth-page">
        <div class="login-page">
            <section class="login-visual">
                <div class="login-copy">
                    <span class="eyebrow gold">RESTAURANTE MESARESERVA</span>
                    <h2>Sua mesa,<br>
                        <span>do seu jeito.</span>
                    </h2>
                    <p>Reserve com praticidade, acompanhe seus pedidos de reserva e fale diretamente com nossa equipe.</p>
                </div>
            </section>
            <section class="login-panel">
                <div class="login-box">
                    <div class="auth-back-row">
                        <a class="auth-back-button" href="<?= url() ?>" onclick="if (window.history.length > 1) { window.history.back(); return false; }">
                            <span class="material-symbols-outlined">arrow_back</span>
                            Voltar
                        </a>
                        <a class="auth-home-button" href="<?= url() ?>">
                            <span class="material-symbols-outlined">home</span>
                            Início
                        </a>
                    </div>
                    <a href="<?= url() ?>">
                        <img class="login-logo" src="<?= url('assets/img/logo.png') ?>" alt="MesaReserva">
                </a>
                <div class="login-welcome">
                    <h1>Bem-vindo</h1>
                    <p>Entre para continuar sua experiência.</p>
                </div>
                <?php $flashMessage = get_flash(); if ($flashMessage): ?>
                <div class="alert alert-<?= e($flashMessage['type']) ?>"><?= e($flashMessage['message']) ?></div>
                <?php endif; ?>
                <?php if($error): ?>
                <div class="login-error">
                    <?= e($error) ?>
                </div>
                <?php endif; ?>
                <form method="post">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <div class="access-switch">
                        <label class="access-option">
                            <input type="radio" name="tipo" value="cliente" <?= $tipo==='cliente'?'checked':'' ?>>
                            <span>
                                <b>Cliente</b>
                                <small>Reservar uma mesa</small>
                            </span>
                        </label>
                        <label class="access-option">
                            <input type="radio" name="tipo" value="admin" <?= $tipo==='admin'?'checked':'' ?>>
                            <span>
                                <b>Administrador</b>
                                <small>Gerenciar restaurante</small>
                            </span>
                        </label>
                    </div>
                    <div class="form-group">
                        <label>E-mail</label>
                        <div class="input-icon">
                            <span class="material-symbols-outlined">mail</span>
                            <input class="form-control" type="email" name="email" placeholder="seu@email.com" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Senha</label>
                        <div class="input-icon password-input-wrap">
                            <span class="material-symbols-outlined input-leading-icon">lock</span>
                            <input id="loginPassword" class="form-control" type="password" name="senha" placeholder="Sua senha" autocomplete="current-password" required>
                            <button class="password-visibility-toggle" type="button" aria-label="Mostrar senha" aria-pressed="false" data-password-target="loginPassword">
                                <span class="material-symbols-outlined">visibility</span>
                            </button>
                        </div>
                    </div>
                    <button class="btn btn-primary login-submit" type="submit">Entrar <span class="material-symbols-outlined">arrow_forward</span>
                    </button>
                </form>
                <div class="login-links">
                    <a href="<?= url('criar-conta.php') ?>">Criar uma conta</a>
                    <a href="<?= url('esqueci-senha.php') ?>">Esqueceu sua senha?</a>
                </div>
            </div>
        </section>
    </div>
<?php require __DIR__.'/partials/footer.php'; ?>
<script>
(() => {
    const toggle = document.querySelector('[data-password-target="loginPassword"]');
    const input = document.getElementById('loginPassword');
    if (!toggle || !input) return;
    toggle.addEventListener('click', () => {
        const show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        toggle.setAttribute('aria-pressed', show ? 'true' : 'false');
        toggle.setAttribute('aria-label', show ? 'Ocultar senha' : 'Mostrar senha');
        toggle.querySelector('.material-symbols-outlined').textContent = show ? 'visibility_off' : 'visibility';
    });
})();
</script>
</body>
</html>
