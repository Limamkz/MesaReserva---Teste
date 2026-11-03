<?php
declare(strict_types=1); require_once __DIR__.'/includes/functions.php';
if(is_logged_in()) redirect(is_admin()?'dashboard.php':'cliente.php');
$error=null; $tipo=$_POST['tipo']??'cliente';
if($_SERVER['REQUEST_METHOD']==='POST'){verify_csrf();$nome=trim($_POST['nome']??'');$email=trim($_POST['email']??'');$senha=$_POST['senha']??'';$conf=$_POST['confirmacao']??'';$tipo=$_POST['tipo']??'cliente';$chave=$_POST['chave_admin']??'';
if(empty($_POST['aceita_privacidade']))$error='Você precisa aceitar a Política de Privacidade para criar sua conta.';elseif(mb_strlen($nome)<3)$error='Digite um nome válido.';elseif(!filter_var($email,FILTER_VALIDATE_EMAIL))$error='Digite um e-mail válido.';elseif(strlen($senha)<8)$error='A senha precisa ter pelo menos 8 caracteres.';elseif($senha!==$conf)$error='As senhas não coincidem.';elseif(!in_array($tipo,['admin','cliente'],true))$error='Tipo de acesso inválido.';elseif($tipo==='admin'&&$chave!=='@MesaReserva_231009')$error='A chave administrativa informada está incorreta.';else{
try{$pdo->beginTransaction();$check=$pdo->prepare('SELECT id FROM usuarios WHERE email=? LIMIT 1');$check->execute([$email]);if($check->fetch())throw new RuntimeException('Este e-mail já está cadastrado.');$clienteId=null;if($tipo==='cliente'){$s=$pdo->prepare('INSERT INTO clientes(nome,email) VALUES(?,?)');$s->execute([$nome,$email]);$clienteId=(int)$pdo->lastInsertId();}$token=verification_token();$s=$pdo->prepare('INSERT INTO usuarios(nome,email,senha,tipo,cliente_id,token_verificacao,token_expira_em) VALUES(?,?,?,?,?,?,DATE_ADD(NOW(),INTERVAL 24 HOUR))');$s->execute([$nome,$email,password_hash($senha,PASSWORD_DEFAULT),$tipo,$clienteId,verification_hash($token)]);$pdo->commit();$_SESSION['verification_email']=$email;try{send_verification_email($nome,$email,$token);$_SESSION['verification_sent']=true;}catch(Throwable $mailError){$_SESSION['verification_sent']=false;$_SESSION['verification_mail_error']=$mailError->getMessage();}redirect('verificar-email.php?created=1');}catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();$error=$e instanceof RuntimeException?$e->getMessage():'Não foi possível criar a conta.';}}
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1.0">
        <title>Criar conta | MesaReserva</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,300..600,0..1,0..200&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="<?= url('assets/css/style.css?v=20261008painel3') ?>">
    </head>
    <body class="auth-page">
        <div class="login-page">
            <section class="login-visual">
                <div class="login-copy">
                    <span class="eyebrow gold">UMA CONTA PARA CADA EXPERIÊNCIA</span>
                    <h2>Entre para o<br>
                        <span>seu próximo momento.</span>
                    </h2>
                    <p>Clientes podem reservar e acompanhar suas solicitações. Administradores têm acesso à gestão completa do restaurante.</p>
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
                    <h1>Criar conta</h1>
                    <p>Escolha como você deseja acessar.</p>
                </div>
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
                                <small>Reservar mesas</small>
                            </span>
                        </label>
                        <label class="access-option">
                            <input type="radio" name="tipo" value="admin" <?= $tipo==='admin'?'checked':'' ?>>
                            <span>
                                <b>Administrador</b>
                                <small>Gestão do restaurante</small>
                            </span>
                        </label>
                    </div>
                    <div class="form-group">
                        <label>Nome completo</label>
                        <input class="form-control" name="nome" value="<?= e($_POST['nome']??'') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>E-mail</label>
                        <input class="form-control" type="email" name="email" value="<?= e($_POST['email']??'') ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="senha">Senha</label>
                        <div class="password-wrap">
                            <input id="senha" class="form-control" type="password" name="senha" minlength="8" autocomplete="new-password" required>
                        </div>
                    </div>
                    <div class="password-rules" id="passwordRules">
                        <div class="password-rules-title">Sua senha precisa atender aos requisitos:</div>
                        <div class="password-rule" data-rule="length">
                            <span class="material-symbols-outlined">radio_button_unchecked</span>8 caracteres ou mais</div>
                                <div class="password-rule" data-rule="letter">
                                    <span class="material-symbols-outlined">radio_button_unchecked</span>Pelo menos uma letra</div>
                                        <div class="password-rule" data-rule="number">
                                            <span class="material-symbols-outlined">radio_button_unchecked</span>Pelo menos um número</div>
                                            </div>
                                            <div class="form-group">
                                                <label for="confirmacao">Confirmar senha</label>
                                                <div class="password-wrap">
                                                    <input id="confirmacao" class="form-control" type="password" name="confirmacao" minlength="8" autocomplete="new-password" required>
                                                </div>
                                                <div id="passwordMatch" class="password-match">
                                                </div>
                                            </div>
                                            <div class="form-group admin-key-field" id="adminKey">
                                                <label>Chave de cadastro administrativo</label>
                                                <input class="form-control" type="password" name="chave_admin" placeholder="Digite a chave fornecida pelo restaurante">
                                                <small>Necessária somente para criar uma conta de administrador.</small>
                                            </div>
                                            <label class="privacy-consent signup-privacy" for="aceita-privacidade"><input id="aceita-privacidade" type="checkbox" name="aceita_privacidade" value="1" required <?= !empty($_POST['aceita_privacidade']) ? 'checked' : '' ?>> <span>Li e aceito as <a href="<?= url('privacidade.php') ?>">Políticas de Privacidade</a>.</span></label>
                                            <button class="btn btn-primary login-submit" type="submit">Criar conta <span class="material-symbols-outlined">arrow_forward</span>
                                            </button>
                                        </form>
                                        <p class="login-note">
                                            <a href="<?= url('login.php') ?>">Já tenho uma conta</a>
                                        </p>
                                    </div>
                                </section>
                            </div>
                            <script>
                                const radios = document.querySelectorAll('input[name=tipo]');
                                const key = document.getElementById('adminKey');
                                const senha = document.getElementById('senha');
                                const confirmacao = document.getElementById('confirmacao');
                                const match = document.getElementById('passwordMatch');

                                function toggle() {
                                key.style.display = document.querySelector('input[name=tipo]:checked').value === 'admin' ? 'flex' : 'none';
                                }

                                radios.forEach((radio) => radio.addEventListener('change', toggle));
                                toggle();

                                function rule(name, ok) {
                                const element = document.querySelector('[data-rule="' + name + '"]');

                                if (!element) {
                                return;
                                }

                                element.classList.toggle('ok', ok);
                                element.querySelector('span').textContent = ok ? 'check_circle' : 'radio_button_unchecked';
                                }

                                function validatePassword() {
                                const value = senha.value;

                                rule('length', value.length >= 8);
                                rule('letter', /[A-Za-zÀ-ÿ]/.test(value));
                                rule('number', /\d/.test(value));
                                }

                                function validateMatch() {
                                if (!confirmacao.value) {
                                match.textContent = '';
                                match.className = 'password-match';
                                return;
                                }

                                const ok = senha.value === confirmacao.value;
                                match.textContent = ok ? 'As senhas coincidem.' : 'As senhas ainda não coincidem.';
                                match.className = 'password-match ' + (ok ? 'ok' : 'error');
                                }

                                senha.addEventListener('input', () => {
                                validatePassword();
                                validateMatch();
                                });

                                confirmacao.addEventListener('input', validateMatch);

                                document.querySelector('form').addEventListener('submit', (event) => {
                                const strong = senha.value.length >= 8
                                && /[A-Za-zÀ-ÿ]/.test(senha.value)
                                && /\d/.test(senha.value)
                                && senha.value === confirmacao.value;

                                if (!strong) {
                                event.preventDefault();
                                validatePassword();
                                validateMatch();
                                senha.focus();
                                }
                                });
                            </script>
                        <?php require __DIR__.'/partials/footer.php'; ?>
</body>
                    </html>
