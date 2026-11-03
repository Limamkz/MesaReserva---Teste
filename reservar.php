<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';
require_login();

if (is_admin()) {
    redirect('dashboard.php');
}

$cid = current_client_id();
if (!$cid) {
    redirect('cliente.php');
}

$error = null;

$clientStmt = $pdo->prepare('SELECT nome, telefone FROM clientes WHERE id = ? LIMIT 1');
$clientStmt->execute([$cid]);
$client = $clientStmt->fetch() ?: ['nome' => current_user_name(), 'telefone' => null];
$telefoneCadastrado = trim((string)($client['telefone'] ?? ''));

$mesas = $pdo->query("SELECT id, numero, capacidade FROM mesas WHERE status='disponivel' ORDER BY numero")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $mesa = (int)($_POST['mesa_id'] ?? 0);
    $data = $_POST['data_reserva'] ?? '';
    $hora = $_POST['hora_reserva'] ?? '';
    $pessoas = (int)($_POST['pessoas'] ?? 0);
    $obs = trim($_POST['observacoes'] ?? '');
    $telefoneInformado = trim($_POST['telefone'] ?? '');

    if ($telefoneCadastrado === '') {
        if ($telefoneInformado === '' || !preg_match('/^[0-9()\s+\-]{8,30}$/', $telefoneInformado)) {
            $error = 'Informe um telefone válido para realizar a reserva.';
        } else {
            $telefoneCadastrado = $telefoneInformado;
            $stmt = $pdo->prepare('UPDATE clientes SET telefone = ? WHERE id = ?');
            $stmt->execute([$telefoneCadastrado, $cid]);
        }
    }

    $stmt = $pdo->prepare("SELECT capacidade FROM mesas WHERE id=? AND status='disponivel'");
    $stmt->execute([$mesa]);
    $cap = (int)$stmt->fetchColumn();

    if (!$error && (!$cap || $pessoas < 1 || $pessoas > $cap)) {
        $error = 'Escolha uma mesa disponível compatível com a quantidade de pessoas.';
    } elseif (!$error && (!$data || !$hora)) {
        $error = 'Informe a data e o horário.';
    } elseif (!$error) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM reservas WHERE mesa_id=? AND data_reserva=? AND hora_reserva=? AND status IN ('pendente','confirmada')");
        $stmt->execute([$mesa, $data, $hora]);

        if ((int)$stmt->fetchColumn() > 0) {
            $error = 'Esse horário já foi solicitado para a mesa escolhida.';
        } else {
            $stmt = $pdo->prepare("INSERT INTO reservas(cliente_id,mesa_id,data_reserva,hora_reserva,pessoas,status,observacoes) VALUES(?,?,?,?,?,'pendente',?)");
            $stmt->execute([$cid, $mesa, $data, $hora, $pessoas, $obs]);
            flash('success', 'Reserva enviada! A administração irá confirmar o horário.');
            redirect('minhas-reservas.php');
        }
    }
}

$pageTitle = 'Reservar mesa';
require __DIR__ . '/partials/client-header.php';
?>

<div class="page-heading">
    <div>
        <span class="section-kicker">RESERVA ONLINE</span>
        <h2>Escolha seu momento</h2>
        <p>Selecione uma mesa disponível, data, horário e quantidade de pessoas.</p>
    </div>
    <div class="availability-card small">
        <strong><?= count($mesas) ?></strong>
        <span>mesas disponíveis</span>
    </div>
</div>

<?php if ($telefoneCadastrado === ''): ?>
    <div class="profile-required-note">
        <span class="material-symbols-outlined">phone</span>
        <div>
            <strong>Telefone necessário para a reserva</strong>
            <span>Você ainda não possui um telefone cadastrado. Informe-o abaixo ou <a href="<?= url('perfil.php') ?>">adicione pelo seu perfil</a>.</span>
        </div>
    </div>
<?php endif; ?>

<section class="card form-card">
    <div class="card-body">
        <?php if ($error): ?>
            <div class="login-error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <div class="form-grid">
                <?php if ($telefoneCadastrado === ''): ?>
                    <div class="form-group full">
                        <label>Telefone para contato *</label>
                        <input class="form-control" type="tel" name="telefone" placeholder="(12) 99999-9999" autocomplete="tel" required value="<?= e($_POST['telefone'] ?? '') ?>">
                        <small class="field-help">Esse número será salvo no seu cadastro para as próximas reservas.</small>
                    </div>
                <?php else: ?>
                    <div class="form-group full">
                        <label>Telefone para contato</label>
                        <div class="saved-contact">
                            <span class="material-symbols-outlined">phone</span>
                            <span><?= e($telefoneCadastrado) ?></span>
                            <a href="<?= url('perfil.php') ?>">Alterar</a>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="form-group full">
                    <label>Mesa *</label>
                    <select class="form-control" name="mesa_id" required>
                        <option value="">Selecione uma mesa</option>
                        <?php foreach ($mesas as $m): ?>
                            <option value="<?= (int)$m['id'] ?>" <?= ((int)($_POST['mesa_id'] ?? 0) === (int)$m['id']) ? 'selected' : '' ?>>Mesa <?= e($m['numero']) ?> — até <?= (int)$m['capacidade'] ?> pessoas</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Data *</label>
                    <input class="form-control" type="date" name="data_reserva" min="<?= date('Y-m-d') ?>" value="<?= e($_POST['data_reserva'] ?? date('Y-m-d')) ?>" required>
                </div>
                <div class="form-group">
                    <label>Horário *</label>
                    <input class="form-control" type="time" name="hora_reserva" value="<?= e($_POST['hora_reserva'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Pessoas *</label>
                    <input class="form-control" type="number" name="pessoas" min="1" max="50" value="<?= e($_POST['pessoas'] ?? '2') ?>" required>
                </div>
                <div class="form-group full">
                    <label>Observações</label>
                    <textarea class="form-control" name="observacoes" placeholder="Ex.: aniversário, necessidade de acessibilidade, preferência..."><?= e($_POST['observacoes'] ?? '') ?></textarea>
                </div>
            </div>
            <div class="form-actions">
                <a class="btn btn-light" href="<?= url('cliente.php') ?>">Voltar</a>
                <button class="btn btn-primary" type="submit">Solicitar reserva <span class="material-symbols-outlined">arrow_forward</span></button>
            </div>
        </form>
    </div>
</section>

<?php require __DIR__ . '/partials/footer.php'; ?>
