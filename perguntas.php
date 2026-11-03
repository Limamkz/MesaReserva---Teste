<?php declare(strict_types=1); require_once __DIR__.'/includes/functions.php'; require_login(); if(is_admin()) redirect('perguntas/index.php');$uid=current_user_id();$error=null;if($_SERVER['REQUEST_METHOD']==='POST'){verify_csrf();$assunto=trim($_POST['assunto']??'');$msg=trim($_POST['mensagem']??'');if(mb_strlen($assunto)<3||mb_strlen($msg)<5)$error='Preencha o assunto e explique sua dúvida ou problema.';else{$s=$pdo->prepare('INSERT INTO perguntas(usuario_id,assunto,mensagem) VALUES(?,?,?)');$s->execute([$uid,$assunto,$msg]);flash('success','Mensagem enviada para a administração.');redirect('perguntas.php');}}$s=$pdo->prepare('SELECT * FROM perguntas WHERE usuario_id=? ORDER BY created_at DESC');$s->execute([$uid]);$perguntas=$s->fetchAll();$pageTitle='Ajuda e atendimento';require __DIR__.'/partials/client-header.php'; ?>
<div class="page-heading">
    <div>
        <span class="section-kicker">ATENDIMENTO</span>
        <h2>Fale com a administração</h2>
        <p>Relate um problema, tire uma dúvida ou envie uma solicitação.</p>
    </div>
</div>
<div class="grid-2">
    <section class="card form-card">
        <div class="card-body">
            <div class="support-intro">
                <strong>Estamos aqui para ajudar.</strong>
                <br>Explique sua dúvida com o máximo de detalhes possível. A administração poderá responder diretamente por esta área.</div>
                <?php if($error): ?>
                <div class="login-error">
                    <?= e($error) ?>
                </div>
                <?php endif; ?>
                <form method="post" class="perguntas-form">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <div class="form-group">
                        <label>Assunto</label>
                        <input class="form-control" name="assunto" placeholder="Ex.: dúvida sobre minha reserva" required>
                    </div>
                    <div class="form-group">
                        <label>Mensagem</label>
                        <textarea class="form-control" name="mensagem" placeholder="Explique o que aconteceu ou o que você precisa..." required>
                        </textarea>
                    </div>
                    <button class="btn btn-primary">Enviar mensagem <span class="material-symbols-outlined">send</span>
                    </button>
                </form>
            </div>
        </section>
        <section class="card">
            <div class="card-header">
                <div>
                    <h3>Minhas mensagens</h3>
                    <span>Acompanhe as respostas</span>
                </div>
            </div>
            <div class="card-body">
                <?php foreach($perguntas as $p): ?>
                <div class="message-card">
                    <div>
                        <strong>
                            <?= e($p['assunto']) ?>
                        </strong>
                        <span class="status-badge <?= $p['status']==='respondida'?'status-concluida':'status-pendente' ?>">
                            <?= e(ucfirst($p['status'])) ?>
                        </span>
                    </div>
                    <p>
                        <?= nl2br(e($p['mensagem'])) ?>
                    </p>
                    <?php if($p['resposta']): ?>
                    <div class="reply">
                        <b>Resposta da administração</b>
                        <p>
                            <?= nl2br(e($p['resposta'])) ?>
                        </p>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; if(!$perguntas): ?>
                <div class="empty-state">
                    <span class="material-symbols-outlined">forum</span>
                    <p>Você ainda não enviou nenhuma mensagem.</p>
                </div>
                <?php endif; ?>
            </div>
        </section>
    </div>
    <?php require __DIR__.'/partials/footer.php'; ?>
