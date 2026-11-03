<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$id=(int)($_GET['id']??0);$consumo=['mesa_id'=>'','descricao'=>'','quantidade'=>1,'valor_unitario'=>''];
if($id){$s=$pdo->prepare("SELECT * FROM consumos WHERE id=?");$s->execute([$id]);$consumo=$s->fetch();if(!$consumo){flash('error','Consumo não encontrado.');redirect('consumos/index.php');}}
$mesas=$pdo->query("SELECT id,numero,status FROM mesas ORDER BY numero")->fetchAll();
$pageTitle=$id?'Editar consumo':'Novo consumo';
require __DIR__.'/../partials/header.php';
?>
<div class="page-heading">
    <div>
        <h2>
            <?= e($pageTitle) ?>
        </h2>
        <p>Lance um item para atualizar o total gasto da mesa.</p>
    </div>
</div>
<section class="card form-card">
    <div class="card-body">
        <form method="post" action="<?= url('consumos/save.php') ?>">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="id" value="<?= $id ?>">
            <div class="form-grid">
                <div class="form-group">
                    <label>Mesa *</label>
                    <select class="form-control" name="mesa_id" required>
                        <option value="">Selecione</option>
                        <?php foreach($mesas as $m): ?>
                        <option value="<?= $m['id'] ?>" <?= (int)$consumo['mesa_id']===(int)$m['id']?'selected':'' ?>>Mesa <?= e($m['numero']) ?> — <?= status_label($m['status']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Descrição *</label>
                    <input class="form-control" name="descricao" value="<?= e($consumo['descricao']) ?>" placeholder="Ex.: Jantar executivo" required>
                </div>
                <div class="form-group">
                    <label>Quantidade *</label>
                    <input class="form-control" type="number" name="quantidade" min="1" step="1" value="<?= e($consumo['quantidade']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Valor unitário (R$) *</label>
                    <input class="form-control" type="number" name="valor_unitario" min="0" step="0.01" value="<?= e($consumo['valor_unitario']) ?>" required>
                </div>
            </div>
            <div class="form-actions">
                <a class="btn btn-light" href="<?= url('consumos/index.php') ?>">Cancelar</a>
                <button class="btn btn-primary">
                    <span class="material-symbols-outlined">save</span>Salvar consumo</button>
                    </div>
                </form>
            </div>
        </section>
        <?php require __DIR__.'/../partials/footer.php'; ?>
