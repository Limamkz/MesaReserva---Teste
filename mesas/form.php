<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$id = (int)($_GET['id'] ?? 0);
$mesa = ['numero'=>'','capacidade'=>2,'status'=>'disponivel','observacoes'=>''];

if ($id) {
$stmt = $pdo->prepare("SELECT * FROM mesas WHERE id = ?");
$stmt->execute([$id]);
$mesa = $stmt->fetch();
if (!$mesa) { flash('error','Mesa não encontrada.'); redirect('mesas/index.php'); }
}
$pageTitle = $id ? 'Editar mesa' : 'Nova mesa';
require __DIR__ . '/../partials/header.php';
?>
<div class="page-heading">
    <div>
        <h2>
            <?= e($pageTitle) ?>
        </h2>
        <p>Cadastre a estrutura da mesa e sua situação atual.</p>
    </div>
</div>
<section class="card form-card">
    <div class="card-body">
        <form method="post" action="<?= url('mesas/save.php') ?>">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="id" value="<?= $id ?>">
            <div class="form-grid">
                <div class="form-group">
                    <label>Número da mesa *</label>
                    <input class="form-control" type="number" name="numero" min="1" value="<?= e($mesa['numero']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Capacidade de pessoas *</label>
                    <input class="form-control" type="number" name="capacidade" min="1" max="50" value="<?= e($mesa['capacidade']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Status *</label>
                    <select class="form-control" name="status" required>
                        <option value="disponivel" <?= $mesa['status']==='disponivel'?'selected':'' ?>>Disponível</option>
                        <option value="reservada" <?= $mesa['status']==='reservada'?'selected':'' ?>>Reservada</option>
                        <option value="ocupada" <?= $mesa['status']==='ocupada'?'selected':'' ?>>Ocupada</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Identificação</label>
                    <input class="form-control" value="Mesa <?= e($mesa['numero'] ?: '—') ?>" disabled>
                </div>
                <div class="form-group full">
                    <label>Observações</label>
                    <textarea class="form-control" name="observacoes" placeholder="Ex.: perto da janela, área externa, acessibilidade...">
                        <?= e($mesa['observacoes']) ?>
                    </textarea>
                </div>
            </div>
            <div class="form-actions">
                <a class="btn btn-light" href="<?= url('mesas/index.php') ?>">Cancelar</a>
                <button class="btn btn-primary" type="submit">
                    <span class="material-symbols-outlined">save</span>Salvar mesa</button>
                    </div>
                </form>
            </div>
        </section>
        <?php require __DIR__ . '/../partials/footer.php'; ?>
