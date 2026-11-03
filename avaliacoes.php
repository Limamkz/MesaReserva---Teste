<?php declare(strict_types=1); require_once __DIR__.'/includes/functions.php'; require_login(); if(is_admin()) redirect('avaliacoes/index.php');$uid=current_user_id();$error=null;if($_SERVER['REQUEST_METHOD']==='POST'){verify_csrf();$nota=(int)($_POST['nota']??0);$comentario=trim($_POST['comentario']??'');if($nota<1||$nota>5)$error='Escolha uma nota de 1 a 5.';else{$s=$pdo->prepare('INSERT INTO avaliacoes(usuario_id,nota,comentario) VALUES(?,?,?)');$s->execute([$uid,$nota,$comentario]);flash('success','Obrigado por avaliar o MesaReserva!');redirect('avaliacoes.php');}}$pageTitle='Avaliações';require __DIR__.'/partials/client-header.php'; ?>
<div class="page-heading">
    <div>
        <span class="section-kicker">SUA OPINIÃO IMPORTA</span>
        <h2>Avalie sua experiência</h2>
        <p>Seu feedback ajuda o Restaurante MesaReserva a melhorar cada detalhe.</p>
    </div>
</div>
<section class="card form-card">
    <div class="card-body">
        <?php if($error): ?>
        <div class="login-error">
            <?= e($error) ?>
        </div>
        <?php endif; ?>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <div class="rating-picker">
                <label>Como você avalia o serviço?</label>
                <div>
                    <?php for($i=5;$i>=1;$i--): ?>
                    <input type="radio" id="star<?= $i ?>" name="nota" value="<?= $i ?>" required>
                    <label for="star<?= $i ?>">★</label>
                    <?php endfor; ?>
                </div>
            </div>
            <div class="form-group">
                <label>Comentário</label>
                <textarea class="form-control" name="comentario" maxlength="1000" placeholder="Conte um pouco sobre sua experiência...">
                </textarea>
            </div>
            <button class="btn btn-primary">Enviar avaliação <span class="material-symbols-outlined">star</span>
            </button>
        </form>
    </div>
</section>
<?php require __DIR__.'/partials/footer.php'; ?>
