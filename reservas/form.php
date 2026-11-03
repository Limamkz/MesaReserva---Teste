<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$id=(int)($_GET['id']??0);
$r=['cliente_id'=>'','mesa_id'=>'','data_reserva'=>date('Y-m-d'),'hora_reserva'=>date('H:i'),'pessoas'=>2,'status'=>'pendente','observacoes'=>''];
if($id){$s=$pdo->prepare("SELECT * FROM reservas WHERE id=?");$s->execute([$id]);$r=$s->fetch();if(!$r){flash('error','Reserva não encontrada.');redirect('reservas/index.php');}}
$clientes=$pdo->query("SELECT id,nome,telefone FROM clientes ORDER BY nome")->fetchAll();
$mesas=$pdo->query("SELECT id,numero,capacidade,status FROM mesas ORDER BY numero")->fetchAll();
$pageTitle=$id?'Editar reserva':'Nova reserva';
require __DIR__.'/../partials/header.php';
?>
<div class="page-heading">
    <div>
        <h2>
            <?= e($pageTitle) ?>
        </h2>
        <p>Associe um cliente a uma mesa, data, horário e quantidade de pessoas.</p>
    </div>
</div>
<section class="card form-card">
    <div class="card-body">
        <form method="post" action="<?= url('reservas/save.php') ?>">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="id" value="<?= $id ?>">
            <div class="form-grid">
                <div class="form-group full">
                    <label>Cliente *</label>
                    <select class="form-control" name="cliente_id" required>
                        <option value="">Selecione um cliente</option>
                        <?php foreach($clientes as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= (int)$r['cliente_id']===(int)$c['id']?'selected':'' ?>>
                            <?= e($c['nome']) ?>
                            <?= $c['telefone']?' — '.e($c['telefone']):'' ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Mesa *</label>
                    <select class="form-control" name="mesa_id" required>
                        <option value="">Selecione uma mesa</option>
                        <?php foreach($mesas as $m): ?>
                        <option value="<?= $m['id'] ?>" data-cap="<?= $m['capacidade'] ?>" <?= (int)$r['mesa_id']===(int)$m['id']?'selected':'' ?>>Mesa <?= e($m['numero']) ?> — <?= (int)$m['capacidade'] ?> lugares — <?= status_label($m['status']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Quantidade de pessoas *</label>
                    <input class="form-control" type="number" name="pessoas" min="1" max="50" value="<?= e($r['pessoas']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Data *</label>
                    <input class="form-control" type="date" name="data_reserva" value="<?= e($r['data_reserva']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Horário *</label>
                    <input class="form-control" type="time" name="hora_reserva" value="<?= e(substr($r['hora_reserva'],0,5)) ?>" required>
                </div>
                <div class="form-group">
                    <label>Status *</label>
                    <select class="form-control" name="status" required>
                        <?php foreach(['pendente','confirmada','cancelada','concluida'] as $st): ?>
                        <option value="<?= $st ?>" <?= $r['status']===$st?'selected':'' ?>>
                            <?= status_label($st) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group full">
                    <label>Observações</label>
                    <textarea class="form-control" name="observacoes" placeholder="Ex.: aniversário, preferência de lugar...">
                        <?= e($r['observacoes']) ?>
                    </textarea>
                </div>
            </div>
            <div class="form-actions">
                <a class="btn btn-light" href="<?= url('reservas/index.php') ?>">Cancelar</a>
                <button class="btn btn-primary">
                    <span class="material-symbols-outlined">save</span>Salvar reserva</button>
                    </div>
                </form>
            </div>
        </section>
        <?php require __DIR__.'/../partials/footer.php'; ?>
