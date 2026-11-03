<?php declare(strict_types=1); require_once __DIR__.'/../config/admin_guard.php';$id=(int)($_GET['id']??0);$s=$pdo->prepare("SELECT p.*,u.nome,u.email FROM perguntas p INNER JOIN usuarios u ON u.id=p.usuario_id WHERE p.id=?");$s->execute([$id]);$p=$s->fetch();if(!$p){flash('error','Mensagem não encontrada.');redirect('perguntas/index.php');}$error=null;if($_SERVER['REQUEST_METHOD']==='POST'){verify_csrf();$resp=trim($_POST['resposta']??'');if(!$resp)$error='Digite uma resposta.';else{$s=$pdo->prepare("UPDATE perguntas SET resposta=?,status='respondida',respondida_em=NOW() WHERE id=?");$s->execute([$resp,$id]);flash('success','Resposta enviada ao cliente.');redirect('perguntas/index.php');}}$pageTitle='Responder cliente';require __DIR__.'/../partials/header.php'; ?>
<div class="page-heading">
    <div>
        <h2>Responder mensagem</h2>
        <p>
            <?= e($p['nome']) ?> · <?= e($p['email']) ?>
        </p>
    </div>
</div>
<section class="card form-card">
    <div class="card-body">
        <div class="message-card">
            <strong>
                <?= e($p['assunto']) ?>
            </strong>
            <p>
                <?= nl2br(e($p['mensagem'])) ?>
            </p>
        </div>
        <?php if($error): ?>
        <div class="login-error">
            <?= e($error) ?>
        </div>
        <?php endif; ?>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <div class="form-group">
                <label>Resposta</label>
                <textarea class="form-control" name="resposta" required>
                    <?= e($p['resposta']??'') ?>
                </textarea>
            </div>
            <div class="form-actions">
                <a class="btn btn-light" href="<?= url('perguntas/index.php') ?>">Cancelar</a>
                <button class="btn btn-primary">Salvar resposta</button>
            </div>
        </form>
    </div>
</section>
<?php require __DIR__.'/../partials/footer.php'; ?>
